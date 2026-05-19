<?php

namespace App\Services\Belpost\Recipient;

use App\Libraries\Belpost\Exceptions\BelpostApiException;
use App\Libraries\Belpost\Facades\ApiBelpostFacade;
use App\Models\Orders\Order;
use App\Services\Belpost\Mappers\BelpostRecipientMapper;
use Illuminate\Support\Arr;

/**
 * Registers recipients in Belpost before items are added to a mailing list.
 */
class BelpostRecipientService
{
    public function __construct(
        private readonly BelpostRecipientMapper $mapper,
    ) {}

    /**
     * Register or update recipient in Belpost and return foreign_id for batch items.
     */
    public function ensureForeignId(Order $order): string
    {
        $foreignId = $this->mapper->foreignIdFor($order);
        $payload = $this->mapper->toPayload($order, $foreignId);

        $response = ApiBelpostFacade::recipientCreate()
            ->request(['data' => [$payload]])
            ->getBodyFormat();

        $created = Arr::get($response, 'created', []);
        if (is_array($created) && $created !== []) {
            return $foreignId;
        }

        // Bulk create may return empty "created" when the recipient already exists — update by Belpost id.
        $existing = $this->findByForeignId($foreignId);

        if ($existing === null) {
            $failed = Arr::get($response, 'failed', []);
            $reason = 'Belpost did not create recipient.';

            if (is_array($failed) && isset($failed[0]) && is_array($failed[0])) {
                $reason = (string)($failed[0]['reason'] ?? $failed[0]['message'] ?? json_encode($failed[0], JSON_UNESCAPED_UNICODE));
            }

            throw new BelpostApiException("Order #{$order->id}: {$reason}");
        }

        ApiBelpostFacade::recipientUpdate((int)$existing['id'])
            ->request($payload);

        return $foreignId;
    }

    /**
     * Paginated scan — API has no filter-by-foreign_id endpoint.
     *
     * @return array<string, mixed>|null
     */
    private function findByForeignId(string $foreignId): ?array
    {
        $page = 1;
        $lastPage = 1;

        while ($page <= $lastPage) {
            $response = ApiBelpostFacade::recipientList()
                ->request([
                    'perPage' => 100,
                    'page' => $page,
                ])
                ->getBodyFormat();

            $lastPage = (int)($response['last_page'] ?? 1);
            $data = Arr::get($response, 'data', []);

            if (!is_array($data)) {
                break;
            }

            foreach ($data as $recipient) {
                if (!is_array($recipient)) {
                    continue;
                }

                if ((string)($recipient['foreign_id'] ?? '') === $foreignId) {
                    return $recipient;
                }
            }

            $page++;
        }

        return null;
    }
}
