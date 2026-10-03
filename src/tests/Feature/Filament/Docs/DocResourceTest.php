<?php

namespace Tests\Feature\Filament\Docs;

use App\Enums\Filament\NavGroup;
use App\Filament\Resources\Docs\DocResource;
use App\Filament\Resources\Docs\Pages\CreateDoc;
use App\Filament\Resources\Docs\Pages\EditDoc;
use App\Filament\Resources\Docs\Pages\ListDocs;
use App\Filament\Resources\Docs\Pages\ViewDoc;
use App\Models\Admin\AdminUser;
use App\Models\Doc;
use Filament\Navigation\NavigationGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DocResourceTest extends TestCase
{
    use RefreshDatabase;

    private AdminUser $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->createSuperAdmin();
    }

    public function test_documents_stay_in_the_docs_group_and_editing_goes_to_settings(): void
    {
        Doc::factory()->create([
            'title' => 'Этапы заказа',
            'slug' => 'manager_manual_orderstep',
            'sort' => 70,
        ]);
        Doc::factory()->create([
            'title' => 'Админ - инструкции',
            'slug' => 'test',
            'sort' => 90,
        ]);

        $this->actingAs($this->admin, 'admin');

        $items = collect(DocResource::getNavigationItems());

        $docLabels = $items
            ->filter(fn ($item): bool => $item->getGroup() === NavGroup::Docs)
            ->sortBy(fn ($item) => $item->getSort())
            ->map(fn ($item): string => $item->getLabel())
            ->values()
            ->all();

        $this->assertSame([
            'Этапы заказа',
            'Админ - инструкции',
        ], $docLabels);

        $editItem = $items->first(fn ($item): bool => $item->getGroup() === NavGroup::Settings);

        $this->assertNotNull($editItem);
        $this->assertSame('Документация', $editItem->getLabel());
    }

    public function test_selected_navigation_groups_start_collapsed(): void
    {
        $groups = filament()->getPanel('admin')->getNavigationGroups();

        foreach (NavGroup::cases() as $case) {
            $group = $groups[$case->name];

            $this->assertInstanceOf(NavigationGroup::class, $group);
            $this->assertSame($case->isCollapsed(), $group->isCollapsed());
            $this->assertTrue($group->isCollapsible());
            $this->assertSame($case->getLabel(), $group->getLabel());
        }
    }

    public function test_doc_page_renders_html(): void
    {
        $doc = Doc::factory()->create([
            'title' => 'Менеджер - Instagram',
            'slug' => 'manager_instagram',
            'html' => '<p><strong>Регламент</strong></p><ul><li>Пункт списка</li></ul><table><tr><td>Принят (new)</td></tr></table>',
        ]);

        $this->actingAs($this->admin, 'admin');

        $component = Livewire::test(ViewDoc::class, ['record' => $doc->slug]);
        $component->assertSuccessful();
        $component->assertSee('Менеджер - Instagram');
        $component->assertSeeHtml('<strong>Регламент</strong>');
        $component->assertSeeHtml('<ul>');
        $component->assertSeeHtml('fi-prose');
        $component->assertSeeHtml('Принят (new)');
    }

    public function test_docs_list_page_can_be_rendered(): void
    {
        $doc = Doc::factory()->create([
            'title' => 'Менеджер - Скрипты',
            'slug' => 'manager_script',
        ]);

        $this->actingAs($this->admin, 'admin');

        $component = Livewire::test(ListDocs::class);
        $component->assertSuccessful();
        $component->assertCanSeeTableRecords([$doc]);
    }

    public function test_doc_can_be_created(): void
    {
        $this->actingAs($this->admin, 'admin');

        $component = Livewire::test(CreateDoc::class);
        $component->assertSuccessful();
        $component
            ->fillForm([
                'title' => 'Новая инструкция',
                'slug' => 'new_manual',
                'sort' => 110,
                'html' => '<p>Текст инструкции</p>',
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $doc = Doc::query()->where('slug', 'new_manual')->first();

        $this->assertNotNull($doc);
        $this->assertSame('Новая инструкция', $doc->title);
        $this->assertSame(110, $doc->sort);
        $this->assertSame('<p>Текст инструкции</p>', $doc->html);
    }

    public function test_doc_can_be_updated(): void
    {
        $doc = Doc::factory()->create([
            'title' => 'Менеджер - Ссылки',
            'slug' => 'manager_utm',
            'sort' => 50,
        ]);

        $this->actingAs($this->admin, 'admin');

        $component = Livewire::test(EditDoc::class, ['record' => $doc->slug]);
        $component->assertSuccessful();
        $component
            ->fillForm([
                'title' => 'Разметка ссылок',
                'slug' => 'manager_utm',
                'sort' => 55,
                'html' => '<p>Новый текст</p>',
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $doc->refresh();

        $this->assertSame('Разметка ссылок', $doc->title);
        $this->assertSame(55, $doc->sort);
        $this->assertSame('<p>Новый текст</p>', $doc->html);
    }

    public function test_doc_title_and_slug_are_required(): void
    {
        $this->actingAs($this->admin, 'admin');

        $component = Livewire::test(CreateDoc::class);
        $component
            ->fillForm([
                'title' => '',
                'slug' => '',
                'sort' => 10,
                'html' => '<p>Текст</p>',
            ])
            ->call('create')
            ->assertHasFormErrors([
                'title' => 'required',
                'slug' => 'required',
            ]);
    }

    private function createSuperAdmin(): AdminUser
    {
        $admin = AdminUser::query()->create([
            'username' => 'doc_admin',
            'password' => bcrypt('secret'),
            'name' => 'Doc Admin',
        ]);

        $role = Role::findOrCreate('super_admin', 'admin');
        $admin->assignRole($role);

        return $admin;
    }
}
