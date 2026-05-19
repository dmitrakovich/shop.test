<?php

namespace App\Libraries\Belpost\Facades;

use App\Libraries\Belpost\Actions\BatchMailingCommitList;
use App\Libraries\Belpost\Actions\BatchMailingCreateList;
use App\Libraries\Belpost\Actions\BatchMailingCreateListItems;
use App\Libraries\Belpost\Actions\BatchMailingDeleteList;
use App\Libraries\Belpost\Actions\BatchMailingDeleteListItem;
use App\Libraries\Belpost\Actions\BatchMailingGenerateListBlank;
use App\Libraries\Belpost\Actions\BatchMailingGenerateListItemBlank;
use App\Libraries\Belpost\Actions\BatchMailingGetDocuments;
use App\Libraries\Belpost\Actions\BatchMailingGetList;
use App\Libraries\Belpost\Actions\BatchMailingGetListItem;
use App\Libraries\Belpost\Actions\BatchMailingUpdateList;
use App\Libraries\Belpost\Actions\BatchMailingUpdateListItem;
use App\Libraries\Belpost\Actions\GeoDirectoryAddresses;
use App\Libraries\Belpost\Actions\GeoDirectoryPostcode;
use App\Libraries\Belpost\Actions\PostcodesAutocomplete;
use App\Libraries\Belpost\Actions\RecipientCreate;
use App\Libraries\Belpost\Actions\RecipientList;
use App\Libraries\Belpost\Actions\RecipientUpdate;
use App\Libraries\Belpost\Api;
use App\Libraries\Belpost\HttpClient;
use Illuminate\Support\Facades\Facade;

/**
 * Facade for the Belpost business API (batch mailing, recipients, geo directory).
 *
 * Each magic method returns an {@see Action} instance; call {@see Action::request()} to execute
 * the HTTP request and receive an {@see \App\Libraries\Belpost\ApiResponse}.
 *
 * @see Api
 *
 * @method static bool isConfigured() Whether BELPOST_API_TOKEN is set.
 * @method static HttpClient httpClient() Underlying HTTP client (paths, downloads).
 * @method static BatchMailingCreateList batchMailingCreateList() POST /batch-mailing/list — create a mailing list.
 * @method static BatchMailingUpdateList batchMailingUpdateList(int $listId) POST /batch-mailing/list/{listId} — update list metadata.
 * @method static BatchMailingGetList batchMailingGetList(?int $listId = null) GET /batch-mailing/list[/{listId}] — fetch one list or the list index.
 * @method static BatchMailingDeleteList batchMailingDeleteList(int $listId) DELETE /batch-mailing/list/{listId}.
 * @method static BatchMailingCommitList batchMailingCommitList(int $listId) POST /batch-mailing/list/{listId}/commit.
 * @method static BatchMailingCreateListItems batchMailingCreateListItems(int $listId) POST /batch-mailing/list/{listId}/item — add items (body: `items`).
 * @method static BatchMailingUpdateListItem batchMailingUpdateListItem(int $listId, int $itemId) PUT /batch-mailing/list/{listId}/item/{itemId}.
 * @method static BatchMailingDeleteListItem batchMailingDeleteListItem(int $listId, int $itemId) DELETE /batch-mailing/list/{listId}/item/{itemId}.
 * @method static BatchMailingGetListItem batchMailingGetListItem(int $listId, int $itemId) GET /batch-mailing/list/{listId}/item/{itemId}.
 * @method static BatchMailingGenerateListBlank batchMailingGenerateListBlank(int $listId) POST /batch-mailing/list/{listId}/generate-blank.
 * @method static BatchMailingGenerateListItemBlank batchMailingGenerateListItemBlank(int $listId, int $itemId) POST /batch-mailing/list/{listId}/item/{itemId}/generate-blank.
 * @method static BatchMailingGetDocuments batchMailingGetDocuments() GET /batch-mailing/documents (supports query: page, perPage).
 * @method static RecipientCreate recipientCreate() POST /batch-mailing/recipient (body: `data`).
 * @method static RecipientUpdate recipientUpdate(int $recipientId) POST /batch-mailing/recipient/{recipientId}.
 * @method static RecipientList recipientList() GET /batch-mailing/recipient (supports query: page, perPage).
 * @method static PostcodesAutocomplete postcodesAutocomplete() GET /postcodes/autocomplete (query: search).
 * @method static GeoDirectoryAddresses geoDirectoryAddresses() GET /business/geo-directory/addresses (query: postcode).
 * @method static GeoDirectoryPostcode geoDirectoryPostcode() GET /business/geo-directory/postcode (query: city, street, building, limit).
 */
class ApiBelpostFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Api::class;
    }
}
