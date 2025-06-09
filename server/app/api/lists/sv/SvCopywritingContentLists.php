<?php

namespace app\api\lists\sv;

use app\api\lists\BaseApiDataLists;
use app\common\lists\ListsSearchInterface;
use app\common\model\sv\SvCopywritingContent;

/**
 * 文案内容列表
 * Class SvCopywritingContentLists
 * @package app\api\lists\sv
 */
class SvCopywritingContentLists extends BaseApiDataLists implements ListsSearchInterface
{
    public function setSearch(): array
    {
        return [
            '=' => ['user_id', 'type', 'copywriting_id'],
            // 其他搜索条件
        ];
    }

    public function lists(): array
    {
        $this->searchWhere[] = ['user_id', '=', $this->userId];
        return SvCopywritingContent::where($this->searchWhere)
            ->order(['id' => 'desc'])
            ->limit($this->limitOffset, $this->limitLength)
            ->select()
            ->toArray();
    }

    public function count(): int
    {
        return SvCopywritingContent::where($this->searchWhere)->count();
    }
}