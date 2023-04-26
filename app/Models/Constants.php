<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Constants extends Model
{
    // TABLE - DEALS, column - type
    const FIRST_CONTACT = 1; // Первый контакт
    const NEGOTIATION = 2; // Переговоры
    const MAKE_DEAL = 3; // Оформление сделки

    // TABLE - DEALS, column - status
    const ACTIVE = 1; // Активный
    const NOT_IMPLEMENTED = 2; // Не реализован
    const COMPLETE = 3; // Завершить

    // TABLE - CLIENTS, column - status
    const CLIENT_ACTIVE = 1; // Активный
    const CLIENT_DELETED = 0; // Removed

    // TABLE - HouseFlat, column - status
    const STATUS_FREE = 0;
    const STATUS_BOOKING = 1;
    const STATUS_SOLD = 2;

    // TABLE - Booking, column - status
    const BOOKING_ACTIVE = 1;
    const BOOKING_ARCHIVE = 0;

    // TABLE - task, column - status
    const DID_IT = 1;  //сделал
    const DID_NOT_DO_IT = 0; //не делал

    // TABLE - pay_status, column - status
    const EIGHTEEN_MONTHS = '18';
    const FIFTEEN_MONTHS = '15';

    // TABLE - pay_status, column - status
    const NOT_PAID = 0; //'Не оплачен';
    const PAID = 1; //'Оплачен';
    const HALF_PAY = 2; //'Частичная оплата';

    // Price formation module, Select LCD (price_type)
    const PRICE_M2 = 1;
    const PRICE_TERRACE = 2;
    const PRICE_ATTIC = 3;
    const PRICE_BASEMENT = 4;

    // Price formation module, Payment % (payment_type)
    const PAYMENT_30 = 30;
    const PAYMENT_50 = 50;
    const PAYMENT_100 = 100;

     // For Pagination pagination - count
     const PAGINATION = 10; // Default
}
