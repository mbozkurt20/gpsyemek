<?php
/**
 * Created by PhpStorm.
 * User: dipok
 * Date: 18/4/20
 * Time: 12:33 PM
 */

namespace App\Enums;

interface OrderStatus
{
    const PENDING    = 5; //PENDING
    const CANCEL     = 10;
    const REJECT     = 12; //UNSUPPLIED
    const ACCEPT     = 14; //CONFIRMED
    const PROCESS    = 15; //PREPARED
    const ASSIGNED = 16; //ASSIGNED
    const ON_THE_WAY = 17; //HANDOVER
    const COMPLETED  = 20; //DELIVERED
}
