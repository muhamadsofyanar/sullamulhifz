#!/usr/bin/env sh
set -eu
cd "$(dirname "$0")/.."
php artisan migrate:status
php artisan tinker --execute='dump([
"students"=>App\Models\Student::where("status","active")->count(),
"teachers"=>App\Models\Teacher::where("status","active")->count(),
"guardians"=>App\Models\Guardian::where("status","active")->count(),
"classes"=>App\Models\SchoolClass::where("status","active")->count(),
"groups"=>App\Models\LearningGroup::where("status","active")->count(),
"public_articles_table"=>Illuminate\Support\Facades\Schema::hasTable("public_articles"),
"report_cards_table"=>Illuminate\Support\Facades\Schema::hasTable("report_cards"),
"import_batches_table"=>Illuminate\Support\Facades\Schema::hasTable("import_batches"),
]);'
curl -fsS http://127.0.0.1:8000/up >/dev/null
echo "Smoke test dasar lulus."
