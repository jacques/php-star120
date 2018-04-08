<?php
/**
 * Star120 Debit Order Parsers and Exporters
 *
 * @author    Jacques Marneweck <jacques@siberia.co.za>
 * @copyright 2018 Jacques Marneweck.  All rights reserved.
 */

namespace Jacques\Star120\Parsers;

use League\Csv\Reader;

class BatchResults
{
    /**
     * Expected headers in the CSV file from Star120.
     *
     * @var array
     */
    protected $headers = [
        'client_name',
        'product_name',
        'debit_reference',
        'client_customer_reference',
        'person_msisdn',
        'client_transaction_reference',
        'bank_account_number',
        'acb_returned_transaction_code',
        'returned_transaction_reason',
        'debit_amount',
    ];

    /**
     * Parse the uploaded batch results file downloaded from the Star120 Debit Engine.
     *
     * @param string $filename
     */
    public function parse($filename)
    {
        $csv = Reader::createFromPath($filename, 'r');

        $response = [
            'interim' => false,
            'data' => [],
        ];

        $headers = $csv->fetchOne();
        if ('*** THIS DATA IS INCOMPLETE AND MAY CHANGE ***' == $headers['0']) {
            $response['interim'] = true;
            $headers = $csv->fetchOne(5);
            $csv->setOffset(6);
        }

        if ($headers !== $this->headers) {
            throw new \Exception('File headers do not match the expected file format.');
        }

        $rows = $csv->fetchAssoc($this->headers);
        foreach ($rows as $index => $row) {
            if ('*** THIS DATA IS INCOMPLETE AND MAY CHANGE ***' == $row['client_name']) {
                break;
            }
            $response['data'][] = $row;
        }

        return ($response);
    }
}
