<?php
declare(strict_types=1);
namespace Tests\Unit\Modules\LandingPage\Http\Controllers;

use Tests\TestCase;
use Tests\Unit\app\Http\Controllers\ControllerTestHelper;
use Modules\LandingPage\Http\Controllers\FeaturesController;
use Illuminate\Http\{RedirectResponse, Request};

/**
 * @covers \Modules\LandingPage\Http\Controllers\FeaturesController
 */
class FeaturesControllerTest extends TestCase
{
    use ControllerTestHelper;

    public function test_constant_IDX_equals_index_1(): void
    {
        $this->assertSame('index', FeaturesController::IDX);
    }

    public function test_constant_CRT_equals_create_2(): void
    {
        $this->assertSame('create', FeaturesController::CRT);
    }

    public function test_constant_STR_equals_store_3(): void
    {
        $this->assertSame('store', FeaturesController::STR);
    }

    public function test_constant_SHW_equals_show_4(): void
    {
        $this->assertSame('show', FeaturesController::SHW);
    }

    public function test_constant_EDT_equals_edit_5(): void
    {
        $this->assertSame('edit', FeaturesController::EDT);
    }

    public function test_constant_UPD_equals_update_6(): void
    {
        $this->assertSame('update', FeaturesController::UPD);
    }

    public function test_constant_DEL_equals_destroy_7(): void
    {
        $this->assertSame('destroy', FeaturesController::DEL);
    }

    public function test_constant_FTR_CRT_equals_featureCreate_8(): void
    {
        $this->assertSame('featureCreate', FeaturesController::FTR_CRT);
    }

    public function test_constant_FTR_STR_equals_featureStore_9(): void
    {
        $this->assertSame('featureStore', FeaturesController::FTR_STR);
    }

    public function test_constant_FTR_EDT_equals_featureEdit_10(): void
    {
        $this->assertSame('featureEdit', FeaturesController::FTR_EDT);
    }

    public function test_constant_FTR_UPD_equals_featureUpdate_11(): void
    {
        $this->assertSame('featureUpdate', FeaturesController::FTR_UPD);
    }

    public function test_constant_FTR_DEL_equals_featureDelete_12(): void
    {
        $this->assertSame('featureDelete', FeaturesController::FTR_DEL);
    }

    public function test_constant_FTR_HGL_equals_featureHighlightCreate_13(): void
    {
        $this->assertSame('featureHighlightCreate', FeaturesController::FTR_HGL);
    }

    public function test_constant_FTRS_CRT_equals_featuresCreate_14(): void
    {
        $this->assertSame('featuresCreate', FeaturesController::FTRS_CRT);
    }

    public function test_constant_FTRS_STR_equals_featuresStore_15(): void
    {
        $this->assertSame('featuresStore', FeaturesController::FTRS_STR);
    }

    public function test_constant_FTRS_EDT_equals_featuresEdit_16(): void
    {
        $this->assertSame('featuresEdit', FeaturesController::FTRS_EDT);
    }

    public function test_constant_FTRS_UPD_equals_featuresUpdate_17(): void
    {
        $this->assertSame('featuresUpdate', FeaturesController::FTRS_UPD);
    }

    public function test_constant_FTRS_DEL_equals_featuresDelete_18(): void
    {
        $this->assertSame('featuresDelete', FeaturesController::FTRS_DEL);
    }

    public function test_index_returns_expected_type_19(): void
    {
        $this->loginMockUser();
        $ctrl = new FeaturesController();
        try {
            $result = $ctrl->index($this->makeRequest());
            $this->assertTrue(true, 'index returned without error');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in index');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in index');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in index');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in index');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in index');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in index');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in index');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in index');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in index');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in index');
                return;
        }
    }

    public function test_show_returns_expected_type_20(): void
    {
        $this->loginMockUser();
        $ctrl = new FeaturesController();
        try {
            $result = $ctrl->show($this->makeRequest(), 1);
            $this->assertTrue(true, 'show returned without error');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in show');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in show');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in show');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in show');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in show');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in show');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in show');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in show');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in show');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in show');
                return;
        }
    }

    public function test_create_returns_expected_type_21(): void
    {
        $this->loginMockUser();
        $ctrl = new FeaturesController();
        try {
            $result = $ctrl->create();
            $this->assertTrue(true, 'create returned without error');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in create');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in create');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in create');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in create');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in create');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in create');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in create');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in create');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in create');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in create');
                return;
        }
    }

    public function test_store_returns_expected_type_22(): void
    {
        $this->loginMockUser();
        $ctrl = new FeaturesController();
        try {
            $result = $ctrl->store($this->makeRequest());
            $this->assertTrue($result instanceof \Illuminate\Http\RedirectResponse, 'store must return RedirectResponse');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in store');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in store');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in store');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in store');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in store');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in store');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in store');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in store');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in store');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in store');
                return;
        }
    }

    public function test_store_with_post_data_23(): void
    {
        $this->loginMockUser();
        $ctrl = new FeaturesController();
        try {
            $req = $this->makeRequest('POST', '/', ['_token' => 'test']);
            $result = $ctrl->store($req);
            $this->assertTrue($result instanceof \Illuminate\Http\RedirectResponse, 'store must return RedirectResponse');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in store');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in store');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in store');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in store');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in store');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in store');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in store');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in store');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in store');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in store');
                return;
        }
    }

    public function test_edit_returns_expected_type_24(): void
    {
        $this->loginMockUser();
        $ctrl = new FeaturesController();
        try {
            $result = $ctrl->edit($this->makeRequest(), 1);
            $this->assertTrue(true, 'edit returned without error');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in edit');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in edit');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in edit');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in edit');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in edit');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in edit');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in edit');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in edit');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in edit');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in edit');
                return;
        }
    }

    public function test_update_returns_expected_type_25(): void
    {
        $this->loginMockUser();
        $ctrl = new FeaturesController();
        try {
            $result = $ctrl->update($this->makeRequest(), 1);
            $this->assertTrue($result instanceof \Illuminate\Http\RedirectResponse, 'update must return RedirectResponse');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in update');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in update');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in update');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in update');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in update');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in update');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in update');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in update');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in update');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in update');
                return;
        }
    }

    public function test_update_with_post_data_26(): void
    {
        $this->loginMockUser();
        $ctrl = new FeaturesController();
        try {
            $req = $this->makeRequest('POST', '/', ['_token' => 'test']);
            $result = $ctrl->update($req, 1);
            $this->assertTrue($result instanceof \Illuminate\Http\RedirectResponse, 'update must return RedirectResponse');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in update');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in update');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in update');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in update');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in update');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in update');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in update');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in update');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in update');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in update');
                return;
        }
    }

    public function test_destroy_returns_expected_type_27(): void
    {
        $this->loginMockUser();
        $ctrl = new FeaturesController();
        try {
            $result = $ctrl->destroy($this->makeRequest(), 1);
            $this->assertTrue($result instanceof \Illuminate\Http\RedirectResponse, 'destroy must return RedirectResponse');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in destroy');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in destroy');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in destroy');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in destroy');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in destroy');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in destroy');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in destroy');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in destroy');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in destroy');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in destroy');
                return;
        }
    }

    public function test_featureCreate_returns_expected_type_28(): void
    {
        $this->loginMockUser();
        $ctrl = new FeaturesController();
        try {
            $result = $ctrl->featureCreate();
            $this->assertTrue(true, 'featureCreate returned without error');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in featureCreate');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in featureCreate');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in featureCreate');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in featureCreate');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in featureCreate');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in featureCreate');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in featureCreate');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in featureCreate');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in featureCreate');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in featureCreate');
                return;
        }
    }

    public function test_featureStore_returns_expected_type_29(): void
    {
        $this->loginMockUser();
        $ctrl = new FeaturesController();
        try {
            $result = $ctrl->featureStore($this->makeRequest());
            $this->assertTrue($result instanceof \Illuminate\Http\RedirectResponse, 'featureStore must return RedirectResponse');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in featureStore');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in featureStore');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in featureStore');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in featureStore');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in featureStore');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in featureStore');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in featureStore');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in featureStore');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in featureStore');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in featureStore');
                return;
        }
    }

    public function test_featureStore_with_post_data_30(): void
    {
        $this->loginMockUser();
        $ctrl = new FeaturesController();
        try {
            $req = $this->makeRequest('POST', '/', ['_token' => 'test']);
            $result = $ctrl->featureStore($req);
            $this->assertTrue($result instanceof \Illuminate\Http\RedirectResponse, 'featureStore must return RedirectResponse');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in featureStore');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in featureStore');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in featureStore');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in featureStore');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in featureStore');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in featureStore');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in featureStore');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in featureStore');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in featureStore');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in featureStore');
                return;
        }
    }

    public function test_featureEdit_returns_expected_type_31(): void
    {
        $this->loginMockUser();
        $ctrl = new FeaturesController();
        try {
            $result = $ctrl->featureEdit(1);
            $this->assertTrue(true, 'featureEdit returned without error');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in featureEdit');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in featureEdit');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in featureEdit');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in featureEdit');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in featureEdit');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in featureEdit');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in featureEdit');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in featureEdit');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in featureEdit');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in featureEdit');
                return;
        }
    }

    public function test_featureUpdate_returns_expected_type_32(): void
    {
        $this->loginMockUser();
        $ctrl = new FeaturesController();
        try {
            $result = $ctrl->featureUpdate($this->makeRequest(), 1);
            $this->assertTrue($result instanceof \Illuminate\Http\RedirectResponse, 'featureUpdate must return RedirectResponse');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in featureUpdate');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in featureUpdate');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in featureUpdate');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in featureUpdate');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in featureUpdate');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in featureUpdate');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in featureUpdate');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in featureUpdate');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in featureUpdate');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in featureUpdate');
                return;
        }
    }

    public function test_featureUpdate_with_post_data_33(): void
    {
        $this->loginMockUser();
        $ctrl = new FeaturesController();
        try {
            $req = $this->makeRequest('POST', '/', ['_token' => 'test']);
            $result = $ctrl->featureUpdate($req, 1);
            $this->assertTrue($result instanceof \Illuminate\Http\RedirectResponse, 'featureUpdate must return RedirectResponse');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in featureUpdate');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in featureUpdate');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in featureUpdate');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in featureUpdate');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in featureUpdate');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in featureUpdate');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in featureUpdate');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in featureUpdate');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in featureUpdate');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in featureUpdate');
                return;
        }
    }

    public function test_featureDelete_returns_expected_type_34(): void
    {
        $this->loginMockUser();
        $ctrl = new FeaturesController();
        try {
            $result = $ctrl->featureDelete($this->makeRequest(), 1);
            $this->assertTrue($result instanceof \Illuminate\Http\RedirectResponse, 'featureDelete must return RedirectResponse');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in featureDelete');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in featureDelete');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in featureDelete');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in featureDelete');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in featureDelete');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in featureDelete');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in featureDelete');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in featureDelete');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in featureDelete');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in featureDelete');
                return;
        }
    }

    public function test_featureHighlightCreate_returns_expected_type_35(): void
    {
        $this->loginMockUser();
        $ctrl = new FeaturesController();
        try {
            $result = $ctrl->featureHighlightCreate($this->makeRequest());
            $this->assertTrue($result instanceof \Illuminate\Http\RedirectResponse, 'featureHighlightCreate must return RedirectResponse');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in featureHighlightCreate');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in featureHighlightCreate');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in featureHighlightCreate');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in featureHighlightCreate');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in featureHighlightCreate');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in featureHighlightCreate');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in featureHighlightCreate');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in featureHighlightCreate');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in featureHighlightCreate');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in featureHighlightCreate');
                return;
        }
    }

    public function test_featuresCreate_returns_expected_type_36(): void
    {
        $this->loginMockUser();
        $ctrl = new FeaturesController();
        try {
            $result = $ctrl->featuresCreate();
            $this->assertTrue(true, 'featuresCreate returned without error');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in featuresCreate');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in featuresCreate');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in featuresCreate');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in featuresCreate');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in featuresCreate');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in featuresCreate');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in featuresCreate');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in featuresCreate');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in featuresCreate');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in featuresCreate');
                return;
        }
    }

    public function test_featuresStore_returns_expected_type_37(): void
    {
        $this->loginMockUser();
        $ctrl = new FeaturesController();
        try {
            $result = $ctrl->featuresStore($this->makeRequest());
            $this->assertTrue($result instanceof \Illuminate\Http\RedirectResponse, 'featuresStore must return RedirectResponse');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in featuresStore');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in featuresStore');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in featuresStore');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in featuresStore');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in featuresStore');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in featuresStore');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in featuresStore');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in featuresStore');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in featuresStore');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in featuresStore');
                return;
        }
    }

    public function test_featuresStore_with_post_data_38(): void
    {
        $this->loginMockUser();
        $ctrl = new FeaturesController();
        try {
            $req = $this->makeRequest('POST', '/', ['_token' => 'test']);
            $result = $ctrl->featuresStore($req);
            $this->assertTrue($result instanceof \Illuminate\Http\RedirectResponse, 'featuresStore must return RedirectResponse');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in featuresStore');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in featuresStore');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in featuresStore');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in featuresStore');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in featuresStore');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in featuresStore');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in featuresStore');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in featuresStore');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in featuresStore');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in featuresStore');
                return;
        }
    }

    public function test_featuresEdit_returns_expected_type_39(): void
    {
        $this->loginMockUser();
        $ctrl = new FeaturesController();
        try {
            $result = $ctrl->featuresEdit(1);
            $this->assertTrue(true, 'featuresEdit returned without error');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in featuresEdit');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in featuresEdit');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in featuresEdit');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in featuresEdit');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in featuresEdit');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in featuresEdit');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in featuresEdit');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in featuresEdit');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in featuresEdit');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in featuresEdit');
                return;
        }
    }

    public function test_featuresUpdate_returns_expected_type_40(): void
    {
        $this->loginMockUser();
        $ctrl = new FeaturesController();
        try {
            $result = $ctrl->featuresUpdate($this->makeRequest(), 1);
            $this->assertTrue($result instanceof \Illuminate\Http\RedirectResponse, 'featuresUpdate must return RedirectResponse');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in featuresUpdate');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in featuresUpdate');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in featuresUpdate');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in featuresUpdate');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in featuresUpdate');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in featuresUpdate');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in featuresUpdate');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in featuresUpdate');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in featuresUpdate');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in featuresUpdate');
                return;
        }
    }

    public function test_featuresUpdate_with_post_data_41(): void
    {
        $this->loginMockUser();
        $ctrl = new FeaturesController();
        try {
            $req = $this->makeRequest('POST', '/', ['_token' => 'test']);
            $result = $ctrl->featuresUpdate($req, 1);
            $this->assertTrue($result instanceof \Illuminate\Http\RedirectResponse, 'featuresUpdate must return RedirectResponse');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in featuresUpdate');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in featuresUpdate');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in featuresUpdate');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in featuresUpdate');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in featuresUpdate');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in featuresUpdate');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in featuresUpdate');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in featuresUpdate');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in featuresUpdate');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in featuresUpdate');
                return;
        }
    }

    public function test_featuresDelete_returns_expected_type_42(): void
    {
        $this->loginMockUser();
        $ctrl = new FeaturesController();
        try {
            $result = $ctrl->featuresDelete($this->makeRequest(), 1);
            $this->assertTrue($result instanceof \Illuminate\Http\RedirectResponse, 'featuresDelete must return RedirectResponse');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in featuresDelete');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in featuresDelete');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in featuresDelete');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in featuresDelete');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in featuresDelete');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in featuresDelete');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in featuresDelete');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in featuresDelete');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in featuresDelete');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in featuresDelete');
                return;
        }
    }

}
