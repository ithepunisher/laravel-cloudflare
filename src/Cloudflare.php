<?php

namespace Novalis\Cloudflare;

use Cloudflare\API\Auth\APIKey as Key;
use Illuminate\Support\Traits\Macroable;
use GuzzleHttp\Exception\ClientException;
use Cloudflare\API\Endpoints\DNS as CF_DNS;
use Cloudflare\API\Endpoints\DNSAnalytics as CF_DNSAnalytics;
use Cloudflare\API\Endpoints\IPs as CF_IPs;
use Cloudflare\API\Adapter\Guzzle as Adapter;

class Cloudflare
{
    use Macroable;

    protected $zone;
    protected $dns;
    protected $ips;
    protected $analytics;

    public function __construct($email, $api, $zone)
    {
        $key = new Key($email, $api);
        $adapter = new Adapter($key);
        $this->zone = $zone;
        $this->dns = new CF_DNS($adapter);
        $this->ips = new CF_IPs($adapter);
        $this->analytics = new CF_DNSAnalytics($adapter);
    }

    /*
     * DNS Queries
     */
    public function addRecord($name, $content = null, $type = 'A', $ttl = 0, $proxied = true)
    {
        if ($content == null && $type = 'A') {
            $content = $_SERVER['SERVER_ADDR'];
        }

        try {
            return $this->dns->addRecord($this->zone, $type, $name, $content, $ttl, $proxied);
        } catch (ClientException $e) {
            return false;
        }
    }

    public function listRecords($info = false, $page = 0, $perPage = 20, $order = '', $direction = '', $type = '', $name = '', $content = '', $match = 'all')
    {
        $records = $this->dns->listRecords($this->zone, $type, $name, $content, $page, $perPage, $order, $direction);

        if ($info) {
            return $records;
        }

        return collect($records->result);
    }

    public function getRecordDetails($recordId)
    {
        return $this->dns->getRecordDetails($this->zone, $recordId);
    }

    public function updateRecordDetails($recordId, array $details)
    {
        return $this->dns->updateRecordDetails($this->zone, $recordId, $details);
    }

    public function deleteRecord($recordId)
    {
        return $this->dns->deleteRecord($this->zone, $recordId);
    }

    /*
     * IP Queries
     */
    public function listIPs()
    {
        return $this->ips->listIPs();
    }

    /**
     * DNS Analytics table
     *
     * @return \stdClass
     */
    public function getReportTable(array $dimensions, array $metrics, array $sort, string $filters, string $since, string $until, int $limit = 100): \stdClass
    {
        return $this->analytics->getReportTable($this->zone, $dimensions, $metrics, $sort, $filters, $since, $until, $limit);
    }
}
