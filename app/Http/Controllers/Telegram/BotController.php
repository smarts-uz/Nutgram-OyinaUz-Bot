<?php

namespace App\Http\Controllers\Telegram;

use App\Handlers\Telegram\TgBotGroupHandler;
use App\Http\Controllers\Controller;
use App\Jobs\TelegramBotButtonActionJob;
use App\Models\Post;
use App\Models\PostUser;
use App\Models\TelegramUser;
use App\Models\TgBot;
use App\Models\TgBotText;
use App\Services\TelegramBotButtonCreator;
use App\Services\TelegramBotGroupService;
use App\Services\TelegramBotService;
use App\Telegram\Middleware\TelegramBotCollectChat;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Http\Request;
use Illuminate\Queue\Queue;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\RunningMode\Polling;
use SergiX44\Nutgram\RunningMode\Webhook;

class BotController extends Controller
{
    protected $botId;
    public function handle(Request $request,$id)
    {
        $this->botId = $id;
        $tgBot = TgBot::where('id', $this->botId)->first();

        $botService = new TelegramBotService();
        $cache = $botService->setCache($this->botId);
//
        $bot = new Nutgram($tgBot->bot_token,
            ['cache' => $cache]);
//
        $tgBot->update(['bot_username' => $bot->getMe()->username]);

        $bot->middleware(function (Nutgram $bot, $next) {
            $bot->setData('bot_id', $this->botId);
            $next($bot);
        });

        $bot->middleware(TelegramBotCollectChat::class);

        $bot->setRunningMode(Webhook::class);

        $bot->onMyChatMember(TgBotGroupHandler::class);


        $bot->onCommand('start', function (Nutgram $bot) {
            $bot->sendMessage('Hello world');
        });

        $bot->onCallbackQuery(function (Nutgram $bot) {
            $callbackData = $bot->callbackQuery()->data;
             $chatId = $bot->callbackQuery()->message->chat->id;
            $keyboardService = new TelegramBotButtonCreator();
            $botService = new TelegramBotService();

            $messageId = $bot->callbackQuery()->message->message_id;
            $userId = $bot->user()->id;


            $post = $keyboardService->findPost($messageId);
            $botText = TgBotText::where('bot_id', $this->botId)->first();
            $isChatMember = $bot->getChatMember($chatId,$userId)->status;
            if ($isChatMember == 'member' || $isChatMember == 'administrator' || $isChatMember == 'creator') {
                $buttonAction = $botService->buttonsAction($messageId,$callbackData,$userId);
                if (!empty($buttonAction)) {
                    switch ($buttonAction) {
                        case 'notRated':
                            $bot->answerCallbackQuery([
                                'text' => $botText->first_action_msg,
                                'cache_time' => 1,
                                'show_alert' => true,
                            ]);
                            $keyboard = $keyboardService->botCreateInlineButtons($post);
                            $bot->editMessageReplyMarkup(['reply_markup' => $keyboard]);
                        case 'rated':
                            $bot->answerCallbackQuery([
                                'text' => $botText->repeated_action_msg,
                                'cache_time' => 1,
                                'show_alert' => true,
                            ]);
                    }
                }
            }else{
                $bot->answerCallbackQuery([
                    'text' => $botText->follow_msg,
                    'cache_time' => 1,
                    'show_alert' => true,
                ]);
            }

        });
        $bot->onNewChatTitle(function (Nutgram $bot) {
            $newTitle = $bot->chat()->title;
            $groupId = $bot->message()->chat->id;
            $service = new TelegramBotGroupService();
            $service->updateChatTitle($groupId,$this->botId,$newTitle);
        });


        $bot->run();
    }
}
