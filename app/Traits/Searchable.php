<?php

namespace App\Traits;

use Elastic\Elasticsearch\Client;

trait Searchable
{
    public function elasticsearchIndex()
    {
        return 'your_index_name';
    }

    public function elasticsearchType()
    {
        return 'your_type_name';
    }

    public function indexToElasticsearch()
    {
        $client = app(Client::class);

        $client->index([
            'index' => $this->elasticsearchIndex(),
            'id' => $this->id,
            'body' => $this->toArray(),
        ]);
    }

    public static function searchElasticsearch($query)
    {
        $client = app(Client::class);

        $response = $client->search([
            'index' => (new static)->elasticsearchIndex(),
            'body'  => [
                'query' => [
                    'match' => [
                        'name' => $query,
                    ]
                ]
            ]
        ]);

        return collect($response['hits']['hits'])->pluck('_source');
    }
}
