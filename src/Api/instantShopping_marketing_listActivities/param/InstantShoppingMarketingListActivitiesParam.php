<?php

/**
 * This file is part of DouDian-SDK
 *
 * @link     https://github.com/westng/doudian-sdk-php
 * @document https://github.com/westng/doudian-sdk/blob/main/README.md
 * @contact  457395070@qq.com
 * @license  https://github.com/westng/doudian-sdk/blob/main/LICENSE
 */

namespace DouDianSdk\Api\instantShopping_marketing_listActivities\param;

class InstantShoppingMarketingListActivitiesParam
{
    /**
     * 活动名称（模糊搜索）.
     *
     * @var string|null
     */
    public $name;

    /**
     * 翻页页码（必填）.
     *
     * @var int
     */
    public $page;

    /**
     * 每页条数（必填）.
     *
     * @var int
     */
    public $size;

    /**
     * 活动状态
     *
     * @var int|null
     */
    public $status;

    /**
     * 活动类型，10：限时限量购，20：店铺券，30：商品券.
     *
     * @var int|null
     */
    public $activity_type;

    /**
     * 活动子类型，101：限时秒杀，102：普通降价促销
     *
     * @var int|null
     */
    public $activity_sub_type;

    /**
     * 活动ID列表.
     *
     * @var array|null
     */
    public $activity_id_list;
}
