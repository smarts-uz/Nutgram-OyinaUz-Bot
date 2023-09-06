<?php

namespace App\MoonShine\Resources;

use App\Models\BotButton;
use App\Models\Media;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Post;

use MoonShine\Actions\ExportAction;
use MoonShine\Actions\ImportAction;
use MoonShine\CKEditor\Fields\CKEditor;
use MoonShine\Decorations\Block;
use MoonShine\Decorations\Column;
use MoonShine\Decorations\Grid;
use MoonShine\Fields\BelongsTo;
use MoonShine\Fields\File;
use MoonShine\Fields\HasMany;
use MoonShine\Fields\HasOne;
use MoonShine\Fields\SwitchBoolean;
use MoonShine\Fields\Text;
use MoonShine\Fields\Textarea;
use MoonShine\Fields\TinyMce;
use MoonShine\Resources\Resource;
use MoonShine\Fields\ID;
use MoonShine\Actions\FiltersAction;
use MoonShine\Trix\Fields\Trix;

class PostResource extends Resource
{
	public static string $model = Post::class;

	public static string $title = 'Posts';
    public string $titleField = 'id';

	public function fields(): array
	{
		return [
            Grid::make([
               Column::make([
                   Block::make(trans('moonshine::ui.custom.basic_info'),[
                       Text::make(trans('moonshine::ui.custom.post_title'),'title')->required()->showOnExport()->useOnImport(),
                       Textarea::make(trans('moonshine::ui.custom.content'),'content')->required()->showOnExport()->useOnImport(),
                   ]),
               ]),
                       Column::make([
                           Block::make(trans('moonshine::ui.custom.media_content'),[
                               HasOne::make(trans('moonshine::ui.custom.add_media'),'media')->fields([
                                   ID::make()->sortable()->showOnExport(),
                                   File::make(trans('moonshine::ui.custom.file'),'file_name')
                                       ->dir('/media')
                                       ->keepOriginalFileName()
                                       ->removable()
                                       ->allowedExtensions([
                                           'jpg',
                                           'jpeg',
                                           'png',
                                           'mp4',
                                           'avi',
                                           'mov',
                                           'mkv',
                                           'wmv',
                                           'mpeg',
                                           'mpg',
                                           '3gp',
                                           'webm',
                                       ]),
                               ])->hideOnIndex()->fullPage()->required(),
                           ]),
                       ])->columnSpan(6),
                       Column::make([
                           Block::make(trans('moonshine::ui.custom.post_buttons'),[
                               HasMany::make(trans('moonshine::ui.custom.button'),'button')->fields([
                                   ID::make(),
                                   Text::make(trans('moonshine::ui.custom.button'),'title'),
                                   Text::make(trans('moonshine::ui.custom.action_count'),'count')->hidden()
                               ])->hideOnIndex()->fullPage()->required(),
                           ]),
                       ])->columnSpan(6),

                        Column::make([
                            Block::make('Bots',[
                                BelongsTo::make('Bot','bot','bot_username'),
//                                BelongsTo::make('Group','group',new TgGroupResource())
                            ])
                        ]),

                        SwitchBoolean::make(trans('moonshine::ui.custom.publish_post'), 'is_published')
                            ->default(false)
                            ->showOnCreate(false)
                            ->showOnDetail(false)
                            ->showOnUpdate(false),
            ]),
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
    public function beforeCreating(Model $item)
    {
        $item->user_id = request()->user()->id;
    }
    protected function afterDeleted(Model $item)
    {
        $media = Media::where('post_id', $item->id)->delete();
        $buttons = BotButton::where('post_id',$item->id)->delete();
    }

    public function actions(): array
    {
        return [
            FiltersAction::make(trans('moonshine::ui.filters')),
            ExportAction::make('Export')->showInLine()
            ->disk('public')
            ->dir('posts'),
            ImportAction::make('Import')->showInLine()
                ->disk('public')
                ->dir('buttons'),
        ];
    }
}
