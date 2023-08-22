<?php

namespace App\MoonShine\Resources;

use Illuminate\Database\Eloquent\Model;
use App\Models\Post;

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

	public function fields(): array
	{
		return [
            Grid::make([
               Column::make([
                   Block::make('Основная информация',[
                       Text::make('Заголовок','title'),
                       Textarea::make('Контент','content'),
                       BelongsTo::make('Company','company_id')->searchable(),
                   ]),
               ]),
                       Column::make([
                           Block::make('Медиа Контет',[
                               HasOne::make('Добавить медиа','media')->fields([
                                   ID::make()->sortable(),
                                   File::make('Файл','file_name')
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
                               ])->hideOnIndex()->fullPage(),
                           ]),
                       ])->columnSpan(6),
                       Column::make([
                           Block::make('Кнопки бота',[
                               HasMany::make('Кнопки', 'button')->fields([
                                   ID::make(),
                                   Text::make('Добавить кнопку','title'),
                               ])->hideOnIndex()->fullPage(),
                           ]),
                       ])->columnSpan(6),

                        SwitchBoolean::make('Опубликовать пост', 'is_published')
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

    public function actions(): array
    {
        return [
            FiltersAction::make(trans('moonshine::ui.filters')),
        ];
    }
}