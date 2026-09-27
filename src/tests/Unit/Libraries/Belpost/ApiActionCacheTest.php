<?php

namespace Tests\Unit\Libraries\Belpost;

use App\Libraries\Belpost\Actions\Action;
use App\Libraries\Belpost\Actions\BatchMailingCreateList;
use App\Libraries\Belpost\Api;
use App\Libraries\Belpost\HttpClient;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class ApiActionCacheTest extends TestCase
{
    public function test_parameterized_actions_keep_the_url_from_each_call(): void
    {
        $api = $this->api();

        $first = $api->__call('batchMailingUpdateListItem', [10, 101]);
        $second = $api->__call('batchMailingUpdateListItem', [10, 202]);

        $this->assertNotSame($first, $second);
        $this->assertSame('/api/v1/business/batch-mailing/list/10/item/101', $this->url($first));
        $this->assertSame('/api/v1/business/batch-mailing/list/10/item/202', $this->url($second));
    }

    public function test_get_list_index_does_not_stick_when_a_list_id_is_passed_later(): void
    {
        $api = $this->api();

        $index = $api->__call('batchMailingGetList', []);
        $firstList = $api->__call('batchMailingGetList', [15]);
        $secondList = $api->__call('batchMailingGetList', [16]);

        $this->assertSame('/api/v1/business/batch-mailing/list', $this->url($index));
        $this->assertSame('/api/v1/business/batch-mailing/list/15', $this->url($firstList));
        $this->assertSame('/api/v1/business/batch-mailing/list/16', $this->url($secondList));
        $this->assertSame($index, $api->__call('batchMailingGetList', []));
    }

    public function test_zero_argument_actions_stay_cached(): void
    {
        $api = $this->api();

        $first = $api->__call('batchMailingCreateList', []);
        $second = $api->__call('batchMailingCreateList', []);

        $this->assertSame($first, $second);
        $this->assertInstanceOf(BatchMailingCreateList::class, $first);
        $this->assertSame('/api/v1/business/batch-mailing/list', $this->url($first));
    }

    private function api(): Api
    {
        return new Api(new HttpClient('https://belpost.test', 'token'));
    }

    private function url(Action $action): string
    {
        return (string)(new ReflectionProperty(Action::class, 'url'))->getValue($action);
    }
}
