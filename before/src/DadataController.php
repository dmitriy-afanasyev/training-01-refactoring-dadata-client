<?php

use DaDataRu;

class DadataController
{
    public function inn($request)
    {
        $datata = new DaDataRu();

        $bin = $datata->getCompanyDataByInn($request);

        if (!$bin) {
            return false;
        }

        return $bin;
    }

    private function bank($request)
    {
        return $response;
    }
}