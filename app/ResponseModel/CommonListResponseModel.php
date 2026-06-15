<?php
namespace App\ResponseModel;

class CommonListResponseModel{
    public int $currentPage;
    public int $totalRecords;
    public array $dataList;

    public function __construct(int $currentPage, int $totalRecords, array $dataList)
    {
        $this->currentPage = $currentPage;
        $this->totalRecords = $totalRecords;
        $this->dataList = $dataList;
    }

    public function toArray(): array
    {
        return [
            "current_page" => $this->currentPage,
            "total_records" => $this->totalRecords,
            "data_list" => $this->dataList
        ];
    }
}