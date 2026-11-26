<?php

namespace App\Services;

use App\Models\Admin\Turnamen;
use App\Models\User\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class TurnamenCreator
{
    public function create(array $data, User $penyelenggara, string $status): Turnamen
    {
        $poster = $data['poster'] ?? null;
        unset($data['poster']);

        if ($poster instanceof UploadedFile) {
            $directory = public_path('uploads/turnamen');
            File::ensureDirectoryExists($directory);
            $filename = $poster->hashName();
            $poster->move($directory, $filename);
            $data['poster'] = 'uploads/turnamen/'.$filename;
        }

        try {
            return Turnamen::create($data + [
                'id_turnamen' => $this->buatId(),
                'id_user' => $penyelenggara->getAuthIdentifier(),
                'status' => $status,
            ]);
        } catch (QueryException $exception) {
            if (isset($data['poster'])) {
                File::delete(public_path($data['poster']));
            }

            throw $exception;
        }
    }

    private function buatId(): string
    {
        do {
            $id = 'TRN'.now()->format('ymd').strtoupper(Str::random(5));
        } while (Turnamen::whereKey($id)->exists());

        return $id;
    }
}
