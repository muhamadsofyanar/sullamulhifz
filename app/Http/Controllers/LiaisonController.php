<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\LiaisonMessage;
use App\Models\LiaisonThread;
use App\Models\MediaAsset;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Services\MediaStorageService;
use App\Services\Communication\CommunicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\View\View;

class LiaisonController extends Controller
{
    public function __construct(private readonly MediaStorageService $media)
    {
    }

    public function index(Request $request): View
    {
        $query = LiaisonThread::with(['student', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->where('institution_id', $request->user()->institution_id);

        $teacherId = $request->user()->hasRole('teacher') ? $request->user()->teacher?->id : null;
        $guardianStudentIds = $request->user()->hasRole('guardian')
            ? ($request->user()->guardian?->students()->pluck('students.id') ?? collect())
            : collect();

        if ($teacherId || $guardianStudentIds->isNotEmpty()) {
            $query->where(function ($scope) use ($teacherId, $guardianStudentIds): void {
                if ($teacherId) {
                    $scope->where('assigned_teacher_id', $teacherId);
                }
                if ($guardianStudentIds->isNotEmpty()) {
                    $method = $teacherId ? 'orWhereIn' : 'whereIn';
                    $scope->{$method}('student_id', $guardianStudentIds);
                }
            });
        } elseif (! $request->user()->hasAnyRole(['superadmin', 'institution_admin', 'head'])) {
            $query->whereRaw('1 = 0');
        }

        return view('liaison.index', ['threads' => $query->latest('last_message_at')->paginate(20)]);
    }

    public function create(Request $request): View
    {
        $students = collect();

        if ($request->user()->hasRole('teacher')) {
            $teacherId = $request->user()->teacher?->id;
            $classIds = TeacherAssignment::query()
                ->where('teacher_id', $teacherId)
                ->where('institution_id', $request->user()->institution_id)
                ->currentlyActive()
                ->whereNotNull('class_id')
                ->pluck('class_id');
            $groupIds = TeacherAssignment::query()
                ->where('teacher_id', $teacherId)
                ->where('institution_id', $request->user()->institution_id)
                ->currentlyActive()
                ->whereNotNull('learning_group_id')
                ->pluck('learning_group_id');
            $students = Student::query()
                ->where('institution_id', $request->user()->institution_id)
                ->where(function ($query) use ($classIds, $groupIds): void {
                    $query->whereHas('enrollments', fn ($q) => $q->whereIn('class_id', $classIds)->where('status', 'active'))
                        ->orWhereHas('groupMemberships', fn ($q) => $q->whereIn('learning_group_id', $groupIds)->where('status', 'active'));
                })
                ->get();
        }

        if ($request->user()->hasRole('guardian')) {
            $guardianStudents = $request->user()->guardian?->students()->get() ?? collect();
            $students = $students->concat($guardianStudents)->unique('id')->values();
        }

        if ($request->user()->hasAnyRole(['superadmin', 'institution_admin', 'head'])) {
            $students = Student::where('institution_id', $request->user()->institution_id)
                ->where('status', 'active')
                ->get();
        }

        $students = $students->sortBy('full_name')->values();

        return view('liaison.create', compact('students'));
    }

    public function store(Request $request): RedirectResponse
    {
        $institutionId = $request->user()->institution_id;
        $data = $request->validate([
            'student_id' => ['required', Rule::exists('students', 'id')->where('institution_id', $institutionId)],
            'category' => ['required', Rule::in(['learning', 'tahsin', 'tahfizh', 'murajaah', 'character', 'health', 'administration', 'consultation'])],
            'subject' => ['required', 'string', 'max:190'],
            'message' => ['nullable', 'string', 'max:5000'],
            'attachment' => ['nullable', File::types(['pdf', 'jpg', 'jpeg', 'png', 'webp', 'mp3', 'm4a', 'wav', 'mp4', 'mov'])->max((int) config('sullam.upload_max_kb', 25600))],
        ]);
        abort_if(blank($data['message'] ?? null) && ! $request->hasFile('attachment'), 422, 'Isi pesan atau lampiran wajib ditambahkan.');

        $student = Student::where('institution_id', $institutionId)->findOrFail($data['student_id']);
        $teacherId = null;
        $actingAsTeacher = $request->user()->hasRole('teacher')
            && $this->teacherCanAccessStudent($request->user()->teacher?->id, $student);
        $actingAsGuardian = $request->user()->hasRole('guardian')
            && $request->user()->guardian?->students()->whereKey($student->id)->exists();
        $actingAsManager = $request->user()->hasAnyRole(['superadmin', 'institution_admin', 'head']);

        abort_unless($actingAsTeacher || $actingAsGuardian || $actingAsManager, 403);

        if ($actingAsTeacher) {
            $teacherId = $request->user()->teacher?->id;
        } else {
            $classId = $student->currentEnrollment?->class_id;
            $teacherId = TeacherAssignment::query()
                ->where('institution_id', $institutionId)
                ->where('class_id', $classId)
                ->currentlyActive()
                ->value('teacher_id');
        }

        $asset = $request->hasFile('attachment')
            ? $this->media->store($request->file('attachment'), $request->user(), 'liaison', 'private', (int) config('sullam.media_retention_days', 180))
            : null;

        try {
            $thread = DB::transaction(function () use ($request, $data, $student, $teacherId, $asset): LiaisonThread {
                $thread = LiaisonThread::create([
                    'institution_id' => $student->institution_id,
                    'student_id' => $student->id,
                    'class_id' => $student->currentEnrollment?->class_id,
                    'category' => $data['category'],
                    'subject' => $data['subject'],
                    'created_by_user_id' => $request->user()->id,
                    'assigned_teacher_id' => $teacherId,
                    'status' => 'active',
                    'last_message_at' => now(),
                ]);
                $message = LiaisonMessage::create([
                    'liaison_thread_id' => $thread->id,
                    'sender_user_id' => $request->user()->id,
                    'message' => $data['message'] ?? '',
                    'message_type' => $asset ? 'attachment' : 'text',
                    ...$this->assetColumns($asset),
                ]);
                if ($asset) {
                    $this->media->link($asset, $message, 'attachment');
                }

                $participants = collect([$request->user()->id]);
                if ($teacherId) {
                    $participants->push(Teacher::find($teacherId)?->user_id);
                }
                foreach ($student->guardians as $guardian) {
                    $participants->push($guardian->user_id);
                }
                foreach ($participants->filter()->unique() as $userId) {
                    DB::table('liaison_participants')->insertOrIgnore([
                        'liaison_thread_id' => $thread->id,
                        'user_id' => $userId,
                        'participant_role' => $userId === $request->user()->id ? 'creator' : 'participant',
                        'joined_at' => now(),
                    ]);
                }

                return $thread;
            });
        } catch (\Throwable $exception) {
            if ($asset instanceof MediaAsset) {
                $this->media->delete($asset);
            }
            throw $exception;
        }

        try {
            $latestMessage = $thread->messages()->latest('id')->first();
            if ($latestMessage) {
                app(CommunicationService::class)->notifyLiaison($latestMessage);
            }
        } catch (\Throwable $exception) {
            report($exception);
        }

        return redirect()->route('liaison.show', $thread)->with('success', 'Catatan buku penghubung berhasil dikirim.');
    }

    public function show(Request $request, LiaisonThread $thread): View
    {
        $this->authorizeThread($request, $thread);
        $thread->load(['student', 'messages.sender', 'messages.mediaAsset']);
        DB::table('liaison_participants')
            ->where('liaison_thread_id', $thread->id)
            ->where('user_id', $request->user()->id)
            ->update(['last_read_at' => now()]);

        if ($request->user()->hasAnyRole(['superadmin', 'institution_admin', 'head'])) {
            ActivityLog::create([
                'institution_id' => $thread->institution_id,
                'user_id' => $request->user()->id,
                'action' => 'view_private',
                'subject_type' => LiaisonThread::class,
                'subject_id' => $thread->id,
                'reason' => 'Membuka percakapan Buku Penghubung melalui kewenangan pengelola.',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return view('liaison.show', compact('thread'));
    }

    public function reply(Request $request, LiaisonThread $thread): RedirectResponse
    {
        $this->authorizeThread($request, $thread);
        $data = $request->validate([
            'message' => ['nullable', 'string', 'max:5000'],
            'attachment' => ['nullable', File::types(['pdf', 'jpg', 'jpeg', 'png', 'webp', 'mp3', 'm4a', 'wav', 'mp4', 'mov'])->max((int) config('sullam.upload_max_kb', 25600))],
        ]);
        abort_if(blank($data['message'] ?? null) && ! $request->hasFile('attachment'), 422, 'Isi pesan atau lampiran wajib ditambahkan.');

        $asset = $request->hasFile('attachment')
            ? $this->media->store($request->file('attachment'), $request->user(), 'liaison/'.$thread->id, 'private', (int) config('sullam.media_retention_days', 180))
            : null;

        try {
            DB::transaction(function () use ($request, $thread, $data, $asset): void {
                $message = LiaisonMessage::create([
                    'liaison_thread_id' => $thread->id,
                    'sender_user_id' => $request->user()->id,
                    'message' => $data['message'] ?? '',
                    'message_type' => $asset ? 'attachment' : 'text',
                    ...$this->assetColumns($asset),
                ]);
                if ($asset) {
                    $this->media->link($asset, $message, 'attachment');
                }
                $thread->update(['last_message_at' => now(), 'status' => 'active']);
            });
        } catch (\Throwable $exception) {
            if ($asset instanceof MediaAsset) {
                $this->media->delete($asset);
            }
            throw $exception;
        }

        try {
            $latestMessage = $thread->messages()->latest('id')->first();
            if ($latestMessage) {
                app(CommunicationService::class)->notifyLiaison($latestMessage);
            }
        } catch (\Throwable $exception) {
            report($exception);
        }

        return back()->with('success', 'Tanggapan berhasil dikirim.');
    }

    private function authorizeThread(Request $request, LiaisonThread $thread): void
    {
        $user = $request->user();
        abort_unless(
            ($user->hasRole('superadmin') && $user->institution_id === null)
            || (int) $thread->institution_id === (int) $user->institution_id,
            404,
        );
        if ($user->hasAnyRole(['institution_admin', 'head', 'superadmin'])) {
            return;
        }
        $allowedAsTeacher = $user->hasRole('teacher')
            && (int) $thread->assigned_teacher_id === (int) $user->teacher?->id;
        $allowedAsGuardian = $user->hasRole('guardian')
            && $user->guardian?->students()->whereKey($thread->student_id)->exists();

        abort_unless($allowedAsTeacher || $allowedAsGuardian, 403);
    }

    private function assetColumns(?MediaAsset $asset): array
    {
        return $asset ? [
            'media_asset_id' => $asset->id,
            'file_path' => null,
            'original_name' => $asset->original_name,
            'mime_type' => $asset->mime_type,
            'file_size' => $asset->file_size,
        ] : [];
    }

    private function teacherCanAccessStudent(?int $teacherId, Student $student): bool
    {
        if (! $teacherId) {
            return false;
        }
        $classIds = TeacherAssignment::query()
            ->where('teacher_id', $teacherId)
            ->where('institution_id', $student->institution_id)
            ->currentlyActive()
            ->whereNotNull('class_id')
            ->pluck('class_id');
        $groupIds = TeacherAssignment::query()
            ->where('teacher_id', $teacherId)
            ->where('institution_id', $student->institution_id)
            ->currentlyActive()
            ->whereNotNull('learning_group_id')
            ->pluck('learning_group_id');

        return $student->enrollments()->whereIn('class_id', $classIds)->where('status', 'active')->exists()
            || $student->groupMemberships()->whereIn('learning_group_id', $groupIds)->where('status', 'active')->exists();
    }
}
