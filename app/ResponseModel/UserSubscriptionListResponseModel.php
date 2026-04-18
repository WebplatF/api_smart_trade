<?php

namespace App\ResponseModel;

class UserSubscriptionListResponseModel
{
    public int $currentPage;
    public int $totalRecords;
    public array $userList;

    public function __construct(int $currentPage, int $totalRecords, array $userList)
    {
        $this->currentPage = $currentPage;
        $this->totalRecords = $totalRecords;
        $this->userList = $userList;
    }

    public function toArray(): array
    {
        return [
            "current_page" => $this->currentPage,
            "total_records" => $this->totalRecords,
            "user_list" => $this->userList
        ];
    }
}
