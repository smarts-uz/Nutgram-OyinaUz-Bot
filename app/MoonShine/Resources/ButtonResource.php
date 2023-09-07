<?php

namespace App\MoonShine\Resources;

use App\Models\BotButton;
use Illuminate\Database\Eloquent\Model;


use MoonShine\Actions\ExportAction;
use MoonShine\Actions\ImportAction;
use MoonShine\Fields\BelongsTo;
use MoonShine\Fields\Text;
use MoonShine\Resources\Resource;
use MoonShine\Fields\ID;
use MoonShine\Actions\FiltersAction;

class ButtonResource extends Resource
{
	public static string $model = BotButton::class;

	public static string $title = 'Buttons';
    public static string $orderField = 'count';
    public static string $orderType = 'desc';
    public static array $activeActions = ['show','delete','edit'];
	public function fields(): array
	{
		return [
		    ID::make()->sortable()->showOnExport()->useOnImport(),
            Text::make(trans('moonshine::ui.custom.buttons'),'title')->showOnExport()->useOnImport(),
            Text::make(trans('moonshine::ui.custom.action_count'),'count')->showOnExport()->useOnImport(),
            Text::make(trans('moonshine::ui.custom.post_title'),'id', function (BotButton $botButton) {
                if (!empty($botButton->post->title)) {
                return $botButton->post->title;
                } else {
                    return null;
                }
            })->hideOnCreate()->hideOnUpdate(),
            BelongsTo::make('Post Id','post')->showOnExport()->useOnImport()->hideOnIndex()->hideOnCreate()->hideOnUpdate(),

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
            ExportAction::make('Export')->showInLine()
                ->disk('public')
                ->dir('buttons'),
            ImportAction::make('Import')->showInLine()
                ->disk('public')
                ->dir('buttons'),
        ];
    }
}
