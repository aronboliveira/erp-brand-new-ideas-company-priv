<?php

namespace App\Traits;

/**
 * Provides standard CRUD route-action constants for controllers.
 *
 * IDX = 'index',  CRT = 'create', STR = 'store',
 * SHW = 'show',   EDT = 'edit',   UPD = 'update', DEL = 'destroy'
 */
trait HasCrudConstants
{
    public const IDX = 'index';
    public const CRT = 'create';
    public const STR = 'store';
    public const SHW = 'show';
    public const EDT = 'edit';
    public const UPD = 'update';
    public const DEL = 'destroy';
}
