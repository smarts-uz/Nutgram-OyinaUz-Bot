<?php

namespace App\Observers;

use App\Models\Post;
use App\Services\TelegramBotButtonCreator;
use App\Services\TelegramBotService;
use Barryvdh\Debugbar\Facades\Debugbar;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\RunningMode\Webhook;

class PostObserver
{
    public function created(Post $post): void
    {

    }


    public function updated(Post $post): void
    {

        $botService = new TelegramBotService();
        $botId = $botService->getBotById($post->bot_id);

        $bot = new Nutgram($botId->bot_token);

        if ($post->isDirty('is_published')) {
            if ($post->is_published == true) {
                $botService->botSendMessage($bot, $post,$botId->id);

            } else {
                $botService->botDeleteMessage($bot, $post,$botId->id);
            }
            $channelId = $post->tg_groups_id;
            $messageId = $post->tg_message_id;
            $tgChatTitle = $tgChatTitle = $post->tg_chat_title;

            $url = "<a href=\"https://t.me/{$tgChatTitle}/{$messageId}\">{$post->url_title}</a>";
            $content = $post->content;

            $caption = "{$content} \n $url";

            if (!empty($channelId)) {
                $botService->createPostTgUrl($post);
                $botService->botEditeMessage($bot,$channelId,$messageId,$caption,$post);
            }
        }

    }

    public function deleted(Post $post): void
    {
    }

    public function restored(Post $post): void
    {
    }

    public function forceDeleted(Post $post): void
    {
    }
}
