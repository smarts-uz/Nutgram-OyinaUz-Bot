<?php

namespace App\Services;

use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TelegramBotFileCheck
{
    public function fileCheck($post)
    {
        try {
            $file_path = $post->media->file_name;
            $file_info = pathinfo($file_path);
            $file_extension = strtolower($file_info['extension']);

            if (in_array($file_extension, ['jpg', 'jpeg', 'png'])) {
                $file_path = "public/".$post->media->file_name;
//                check if exists
                if (Storage::disk('local')->exists($file_path)) {

                    $fileContents = storage_path('app/'.$file_path);
//                $fileContents = public_path('storage/' . $post->media->file_name);
//                $fileContents = Storage::disk('local')->get($file_path);
                    return ['photo' , $fileContents];
                } else {

                    Debugbar::info('Telegram Bot File Not Found');
                }

            } elseif (in_array($file_extension, ['mp4', 'avi', 'mov', 'mkv', 'wmv', 'mpeg', 'mpg', '3gp', 'webm',])) {
                $file_path = "public/".$post->media->file_name;

//                $fileContents = public_path('storage/' . $post->media->file_name);
                if (Storage::disk('local')->exists($file_path)) {

                    $fileContents = storage_path('app/'.$file_path);
//                $fileContents = public_path('storage/' . $post->media->file_name);
//                $fileContents = Storage::disk('local')->get($file_path);
                    return ['video' , $fileContents];
                } else {

                    Debugbar::info('Telegram Bot File Not Found');
                }
            }

        } catch (\Exception $exception) {
            Debugbar::info($exception);
        }
        return null;
    }

    public function closeFile($photo): void
    {
        if (is_resource($photo)) {
            fclose($photo);
        }
    }
}
