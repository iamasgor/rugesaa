<?php

namespace App\Filament\Pages\SitePages;

use App\Enums\SitePage;
use App\Models\PageContent;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use UnitEnum;

/**
 * Edits the copy and images of one public page, stored in `page_contents`.
 *
 * @property-read Schema $form
 */
abstract class EditSitePage extends Page
{
    protected static string|UnitEnum|null $navigationGroup = 'Website';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    abstract protected static function sitePage(): SitePage;

    /**
     * @return array<Component>
     */
    abstract protected function formComponents(): array;

    public function mount(): void
    {
        $this->form->fill(static::sitePage()->content());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components($this->formComponents())
            ->statePath('data');
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')
                            ->label('Save changes')
                            ->submit('save')
                            ->keyBindings(['mod+s']),
                        Action::make('view')
                            ->label('View page')
                            ->color('gray')
                            ->url(fn (): string => $this->publicUrl())
                            ->openUrlInNewTab(),
                    ])->sticky(),
                ]),
        ]);
    }

    public function save(): void
    {
        /** @var array<string, array<string, mixed>> $state */
        $state = $this->form->getState();

        PageContent::query()->updateOrCreate(
            ['page' => static::sitePage()],
            ['content' => self::withoutRepeaterKeys($state)],
        );

        Notification::make()->title('Page saved')->success()->send();
    }

    abstract protected function publicUrl(): string;

    /**
     * Repeaters key their items by UUID; store plain lists instead.
     *
     * @param  array<array-key, mixed>  $state
     * @return array<array-key, mixed>
     */
    private static function withoutRepeaterKeys(array $state): array
    {
        foreach ($state as $key => $value) {
            if (is_array($value)) {
                $value = self::withoutRepeaterKeys($value);
                $state[$key] = array_is_list($value) || ! self::hasOnlyUuidKeys($value) ? $value : array_values($value);
            }
        }

        return $state;
    }

    /**
     * @param  array<array-key, mixed>  $value
     */
    private static function hasOnlyUuidKeys(array $value): bool
    {
        foreach (array_keys($value) as $key) {
            if (! is_string($key) || ! str($key)->isUuid()) {
                return false;
            }
        }

        return $value !== [];
    }
}
