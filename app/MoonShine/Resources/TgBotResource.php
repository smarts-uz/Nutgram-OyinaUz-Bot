<?php

namespace App\MoonShine\Resources;


use App\Jobs\ArtisanJob;
use App\Models\BotButton;
use App\Models\Media;
use App\Models\TgBotText;
use App\Models\TgGroup;
use App\Services\BotSetWebhookService;
use App\Services\TelegramBotService;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Database\Eloquent\Model;
use App\Models\TgBot;

use Illuminate\Support\Facades\Artisan;
use MoonShine\Fields\BelongsTo;
use MoonShine\Fields\HasMany;
use MoonShine\Fields\Text;
use MoonShine\Fields\Url;
use MoonShine\ItemActions\ItemAction;
use MoonShine\Resources\Resource;
use MoonShine\Fields\ID;
use MoonShine\Actions\FiltersAction;

class TgBotResource extends Resource
{
	public static string $model = TgBot::class;

	public static string $title = 'TgBots';



	public function fields(): array
	{
		return [
		    ID::make()->sortable(),
            Text::make('Bot Token','bot_token')->hideOnIndex()->required(),
            Url::make('Base Url','base_url')->required(),
            Text::make('Bot Username','bot_username')->hideOnCreate()->hideOnUpdate(),
            HasMany::make('BotGroup','groups',new TgGroupResource())->resourceMode(),
        ];
	}

    public function rules(Model $item): array
	{
	    return [];
    }

    public function search(): array
    {
        return ['id'];
    }

    public function filters(): array
    {
        return [];
    }

    public function actions(): array
    {
        return [
            FiltersAction::make(trans('moonshine::ui.filters')),
        ];
    }

    public function beforeCreating(Model $item)
    {
        $item->user_id = request()->user()->id;
    }
    protected function afterDeleted(Model $item)
    {
        $botText = TgBotText::where('bot_id', $item->id)->delete();
        $botGroups = TgGroup::where('tg_bot_id',$item->id)->delete();
    }
    protected function afterCreated(Model $item)
    {
        $botText = new TgBotText();
        $botText->create([
            'first_action_msg' => 'Thanks for your vote',
            'repeated_action_msg' => 'You have already voted',
            'follow_msg' => 'To vote you need to be subscribed to the channel',
            'bot_id' => $item->id,
        ]);
    }
    public function itemActions(): array
    {
        return [
            ItemAction::make('Activate', function (Model $item) {

                $makeBot = new BotSetWebhookService();
                $makeBot->setWebhook($item->id);

            },'Activated')->icon('heroicons.check'),
             ItemAction::make('Deactivate', function (Model $item) {

                 $makeBot = new BotSetWebhookService();
                 $makeBot->deleteWebhook($item->bot_token);

             },'Deactivated')->icon('heroicons.x-mark')
        ];
    }
}
