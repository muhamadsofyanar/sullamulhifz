<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassEnrollment;
use App\Models\Guardian;
use App\Models\ImportBatch;
use App\Models\ImportRow;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportController extends Controller
{
    private const HEADERS = [
        'student_code','full_name','nickname','gender','birth_place','birth_date','address','class_code',
        'guardian_name','guardian_email','guardian_phone','guardian_relationship','guardian_password',
    ];

    public function index(Request $request): View
    {
        return view('admin.imports.index', [
            'batches' => ImportBatch::where('institution_id', $request->user()->institution_id)->latest()->paginate(15),
        ]);
    }

    public function template(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, self::HEADERS);
            fputcsv($out, ['SAN-00001','Nama Santri','Nama Panggilan','male','Bandung','2018-01-01','Alamat','TAMHIDI-A','Nama Wali','wali@example.test','628000000001','ayah','GantiPasswordAwal2026!']);
            fclose($out);
        }, 'template-impor-santri-wali.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function preview(Request $request): View
    {
        $data = $request->validate(['file' => ['required','file','mimes:csv,txt','max:5120']]);
        $handle = fopen($data['file']->getRealPath(), 'r');
        abort_unless($handle, 422, 'File tidak dapat dibaca.');

        $header = fgetcsv($handle) ?: [];
        $header = array_map(fn ($value) => Str::snake(trim((string) $value)), $header);
        abort_unless(count(array_intersect(self::HEADERS, $header)) >= 8, 422, 'Header CSV tidak sesuai dengan template.');

        $institutionId = $request->user()->institution_id;
        $batch = ImportBatch::create([
            'institution_id' => $institutionId,
            'uploaded_by_user_id' => $request->user()->id,
            'type' => 'students_guardians',
            'original_name' => $data['file']->getClientOriginalName(),
            'status' => 'preview',
        ]);

        $rowNumber = 1;
        $valid = 0;
        $invalid = 0;
        while (($values = fgetcsv($handle)) !== false) {
            $rowNumber++;
            if (count(array_filter($values, fn ($v) => filled($v))) === 0) {
                continue;
            }
            $payload = [];
            foreach ($header as $index => $key) {
                $payload[$key] = isset($values[$index]) ? trim((string) $values[$index]) : null;
            }
            $errors = $this->validateRow($payload, $institutionId);
            $status = $errors ? 'invalid' : 'valid';
            $errors ? $invalid++ : $valid++;
            ImportRow::create([
                'import_batch_id' => $batch->id,
                'row_number' => $rowNumber,
                'payload' => $payload,
                'status' => $status,
                'error_message' => $errors ? implode(' ', $errors) : null,
            ]);
        }
        fclose($handle);

        $batch->update([
            'total_rows' => $valid + $invalid,
            'success_rows' => $valid,
            'failed_rows' => $invalid,
            'summary' => ['valid' => $valid, 'invalid' => $invalid],
        ]);
        $batch->load('rows');

        return view('admin.imports.preview', compact('batch'));
    }

    public function show(Request $request, ImportBatch $batch): View
    {
        $this->guardBatch($request, $batch);
        $batch->load('rows');
        return view('admin.imports.preview', compact('batch'));
    }

    public function commit(Request $request, ImportBatch $batch): RedirectResponse
    {
        $this->guardBatch($request, $batch);
        abort_unless($batch->status === 'preview', 422, 'Batch ini sudah diproses.');
        abort_if($batch->failed_rows > 0, 422, 'Perbaiki seluruh baris tidak valid sebelum melakukan impor.');

        $institutionId = $request->user()->institution_id;
        $guardianRole = Role::where('name', 'guardian')->firstOrFail();
        $success = 0;

        DB::transaction(function () use ($batch, $institutionId, $guardianRole, &$success): void {
            foreach ($batch->rows()->where('status', 'valid')->orderBy('row_number')->get() as $row) {
                $p = $row->payload;
                $class = SchoolClass::where('institution_id', $institutionId)->where('code', $p['class_code'])->where('status', 'active')->firstOrFail();

                $studentPayload = [
                    'institution_id' => $institutionId,
                    'full_name' => $p['full_name'],
                    'nickname' => $p['nickname'] ?: null,
                    'gender' => $p['gender'] ?: null,
                    'birth_place' => $p['birth_place'] ?: null,
                    'birth_date' => $p['birth_date'] ?: null,
                    'address' => $p['address'] ?: null,
                    'joined_at' => now()->toDateString(),
                    'status' => 'active',
                    'stifin_status' => 'untested',
                ];
                $student = filled($p['student_code'])
                    ? Student::updateOrCreate(['institution_id'=>$institutionId,'student_code'=>$p['student_code']], $studentPayload)
                    : Student::create($studentPayload);
                if (! $student->student_code) {
                    $student->update(['student_code' => 'SAN-'.str_pad((string) $student->id, 5, '0', STR_PAD_LEFT)]);
                }

                $previousEnrollment = ClassEnrollment::query()
                    ->where('student_id', $student->id)
                    ->where('status', 'active')
                    ->latest('enrolled_at')
                    ->first();
                ClassEnrollment::query()
                    ->where('student_id', $student->id)
                    ->where('status', 'active')
                    ->update(['status' => 'moved', 'ended_at' => now()->toDateString()]);
                ClassEnrollment::updateOrCreate(
                    ['class_id'=>$class->id,'student_id'=>$student->id,'academic_year_id'=>$class->academic_year_id],
                    [
                        'enrolled_at'=>now()->toDateString(),
                        'ended_at'=>null,
                        'status'=>'active',
                        'previous_enrollment_id'=>$previousEnrollment?->id,
                    ]
                );

                if (filled($p['guardian_name']) && filled($p['guardian_phone'])) {
                    $user = User::where('institution_id', $institutionId)
                        ->where(fn ($q) => $q->where('phone', $p['guardian_phone'])->when(filled($p['guardian_email']), fn ($qq) => $qq->orWhere('email', $p['guardian_email'])))
                        ->first();
                    if (! $user) {
                        $user = User::create([
                            'institution_id'=>$institutionId,
                            'name'=>$p['guardian_name'],
                            'email'=>$p['guardian_email'] ?: null,
                            'phone'=>$p['guardian_phone'],
                            'password'=>Hash::make($p['guardian_password']),
                            'status'=>'active',
                            'must_change_password'=>true,
                        ]);
                    }
                    $user->roles()->syncWithoutDetaching([$guardianRole->id => ['institution_id'=>$institutionId,'status'=>'active']]);
                    $guardian = Guardian::updateOrCreate(
                        ['user_id'=>$user->id],
                        ['institution_id'=>$institutionId,'full_name'=>$p['guardian_name'],'phone'=>$p['guardian_phone'],'email'=>$p['guardian_email'] ?: null,'status'=>'active']
                    );
                    $student->guardians()->syncWithoutDetaching([$guardian->id => [
                        'relationship'=>$p['guardian_relationship'] ?: 'wali',
                        'is_primary_contact'=>true,
                        'can_receive_notifications'=>true,
                        'can_submit_assignments'=>true,
                        'can_view_learning_records'=>true,
                    ]]);
                }

                $row->update(['status' => 'imported']);
                $success++;
            }
            $batch->update(['status'=>'completed','success_rows'=>$success,'failed_rows'=>0]);
        });

        return redirect()->route('admin.imports.show', $batch)->with('success', $success.' baris berhasil diimpor.');
    }

    private function validateRow(array $row, int $institutionId): array
    {
        $errors = [];
        if (blank($row['full_name'] ?? null)) $errors[] = 'Nama santri wajib diisi.';
        if (blank($row['class_code'] ?? null) || ! SchoolClass::where('institution_id',$institutionId)->where('code',$row['class_code'])->where('status','active')->exists()) {
            $errors[] = 'Kode kelas tidak ditemukan.';
        }
        if (filled($row['gender'] ?? null) && ! in_array($row['gender'], ['male','female'], true)) $errors[] = 'Gender harus male/female.';
        if (filled($row['birth_date'] ?? null) && strtotime($row['birth_date']) === false) $errors[] = 'Tanggal lahir tidak valid.';
        if (filled($row['guardian_name'] ?? null) xor filled($row['guardian_phone'] ?? null)) $errors[] = 'Nama dan nomor wali harus diisi bersama.';
        if (filled($row['guardian_name'] ?? null)) {
            $password = (string) ($row['guardian_password'] ?? '');
            if (mb_strlen($password) < 12 || ! preg_match('/[A-Z]/', $password) || ! preg_match('/[a-z]/', $password) || ! preg_match('/[0-9]/', $password)) {
                $errors[] = 'Password wali minimal 12 karakter dan memuat huruf besar, huruf kecil, serta angka.';
            }
        }
        return $errors;
    }

    private function guardBatch(Request $request, ImportBatch $batch): void
    {
        abort_unless($batch->institution_id === $request->user()->institution_id, 404);
    }
}
