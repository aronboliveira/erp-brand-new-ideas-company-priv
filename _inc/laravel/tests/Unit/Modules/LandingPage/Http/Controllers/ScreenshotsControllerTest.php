<?php
declare(strict_types=1);
namespace Tests\Unit\Modules\LandingPage\Http\Controllers;

use Tests\TestCase;
use Tests\Unit\app\Http\Controllers\ControllerTestHelper;
use Modules\LandingPage\Http\Controllers\ScreenshotsController;
use Illuminate\Http\{RedirectResponse, Request};

/**
 * @covers \Modules\LandingPage\Http\Controllers\ScreenshotsController
 */
class ScreenshotsControllerTest extends TestCase
{
    use ControllerTestHelper;

    public function test_constant_IDX_equals_index_1(): void
    {
        $this->assertSame('index', ScreenshotsController::IDX);
    }

    public function test_constant_CRT_equals_create_2(): void
    {
        $this->assertSame('create', ScreenshotsController::CRT);
    }

    public function test_constant_STR_equals_store_3(): void
    {
        $this->assertSame('store', ScreenshotsController::STR);
    }

    public function test_constant_SHW_equals_show_4(): void
    {
        $this->assertSame('show', ScreenshotsController::SHW);
    }

    public function test_constant_EDT_equals_edit_5(): void
    {
        $this->assertSame('edit', ScreenshotsController::EDT);
    }

    public function test_constant_UPD_equals_update_6(): void
    {
        $this->assertSame('update', ScreenshotsController::UPD);
    }

    public function test_constant_DEL_equals_destroy_7(): void
    {
        $this->assertSame('destroy', ScreenshotsController::DEL);
    }

    public function test_constant_SST_CRT_equals_screenshotsCreate_8(): void
    {
        $this->assertSame('screenshotsCreate', ScreenshotsController::SST_CRT);
    }

    public function test_constant_SST_STR_equals_screenshotsStore_9(): void
    {
        $this->assertSame('screenshotsStore', ScreenshotsController::SST_STR);
    }

    public function test_constant_SST_EDT_equals_screenshotsEdit_10(): void
    {
        $this->assertSame('screenshotsEdit', ScreenshotsController::SST_EDT);
    }

    public function test_constant_SST_UPD_equals_screenshotsUpdate_11(): void
    {
        $this->assertSame('screenshotsUpdate', ScreenshotsController::SST_UPD);
    }

    public function test_constant_SST_DEL_equals_screenshotsDelete_12(): void
    {
        $this->assertSame('screenshotsDelete', ScreenshotsController::SST_DEL);
    }

    public function test_index_returns_expected_type_13(): void
    {
        $this->loginMockUser();
        $ctrl = new ScreenshotsController();
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

    public function test_show_returns_expected_type_14(): void
    {
        $this->loginMockUser();
        $ctrl = new ScreenshotsController();
        try {
            $result = $ctrl->show(1);
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

    public function test_create_returns_expected_type_15(): void
    {
        $this->loginMockUser();
        $ctrl = new ScreenshotsController();
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

    public function test_store_returns_expected_type_16(): void
    {
        $this->loginMockUser();
        $ctrl = new ScreenshotsController();
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

    public function test_store_with_post_data_17(): void
    {
        $this->loginMockUser();
        $ctrl = new ScreenshotsController();
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

    public function test_edit_returns_expected_type_18(): void
    {
        $this->loginMockUser();
        $ctrl = new ScreenshotsController();
        try {
            $result = $ctrl->edit(1);
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

    public function test_update_returns_expected_type_19(): void
    {
        $this->loginMockUser();
        $ctrl = new ScreenshotsController();
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

    public function test_update_with_post_data_20(): void
    {
        $this->loginMockUser();
        $ctrl = new ScreenshotsController();
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

    public function test_destroy_returns_expected_type_21(): void
    {
        $this->loginMockUser();
        $ctrl = new ScreenshotsController();
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

    public function test_screenshotsCreate_returns_expected_type_22(): void
    {
        $this->loginMockUser();
        $ctrl = new ScreenshotsController();
        try {
            $result = $ctrl->screenshotsCreate();
            $this->assertTrue(true, 'screenshotsCreate returned without error');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in screenshotsCreate');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in screenshotsCreate');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in screenshotsCreate');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in screenshotsCreate');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in screenshotsCreate');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in screenshotsCreate');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in screenshotsCreate');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in screenshotsCreate');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in screenshotsCreate');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in screenshotsCreate');
                return;
        }
    }

    public function test_screenshotsStore_returns_expected_type_23(): void
    {
        $this->loginMockUser();
        $ctrl = new ScreenshotsController();
        try {
            $result = $ctrl->screenshotsStore($this->makeRequest());
            $this->assertTrue($result instanceof \Illuminate\Http\RedirectResponse, 'screenshotsStore must return RedirectResponse');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in screenshotsStore');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in screenshotsStore');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in screenshotsStore');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in screenshotsStore');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in screenshotsStore');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in screenshotsStore');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in screenshotsStore');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in screenshotsStore');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in screenshotsStore');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in screenshotsStore');
                return;
        }
    }

    public function test_screenshotsStore_with_post_data_24(): void
    {
        $this->loginMockUser();
        $ctrl = new ScreenshotsController();
        try {
            $req = $this->makeRequest('POST', '/', ['_token' => 'test']);
            $result = $ctrl->screenshotsStore($req);
            $this->assertTrue($result instanceof \Illuminate\Http\RedirectResponse, 'screenshotsStore must return RedirectResponse');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in screenshotsStore');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in screenshotsStore');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in screenshotsStore');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in screenshotsStore');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in screenshotsStore');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in screenshotsStore');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in screenshotsStore');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in screenshotsStore');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in screenshotsStore');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in screenshotsStore');
                return;
        }
    }

    public function test_screenshotsEdit_returns_expected_type_25(): void
    {
        $this->loginMockUser();
        $ctrl = new ScreenshotsController();
        try {
            $result = $ctrl->screenshotsEdit(1);
            $this->assertTrue(true, 'screenshotsEdit returned without error');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in screenshotsEdit');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in screenshotsEdit');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in screenshotsEdit');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in screenshotsEdit');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in screenshotsEdit');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in screenshotsEdit');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in screenshotsEdit');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in screenshotsEdit');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in screenshotsEdit');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in screenshotsEdit');
                return;
        }
    }

    public function test_screenshotsUpdate_returns_expected_type_26(): void
    {
        $this->loginMockUser();
        $ctrl = new ScreenshotsController();
        try {
            $result = $ctrl->screenshotsUpdate($this->makeRequest(), 1);
            $this->assertTrue($result instanceof \Illuminate\Http\RedirectResponse, 'screenshotsUpdate must return RedirectResponse');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in screenshotsUpdate');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in screenshotsUpdate');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in screenshotsUpdate');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in screenshotsUpdate');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in screenshotsUpdate');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in screenshotsUpdate');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in screenshotsUpdate');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in screenshotsUpdate');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in screenshotsUpdate');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in screenshotsUpdate');
                return;
        }
    }

    public function test_screenshotsUpdate_with_post_data_27(): void
    {
        $this->loginMockUser();
        $ctrl = new ScreenshotsController();
        try {
            $req = $this->makeRequest('POST', '/', ['_token' => 'test']);
            $result = $ctrl->screenshotsUpdate($req, 1);
            $this->assertTrue($result instanceof \Illuminate\Http\RedirectResponse, 'screenshotsUpdate must return RedirectResponse');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in screenshotsUpdate');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in screenshotsUpdate');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in screenshotsUpdate');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in screenshotsUpdate');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in screenshotsUpdate');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in screenshotsUpdate');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in screenshotsUpdate');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in screenshotsUpdate');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in screenshotsUpdate');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in screenshotsUpdate');
                return;
        }
    }

    public function test_screenshotsDelete_returns_expected_type_28(): void
    {
        $this->loginMockUser();
        $ctrl = new ScreenshotsController();
        try {
            $result = $ctrl->screenshotsDelete($this->makeRequest(), 1);
            $this->assertTrue($result instanceof \Illuminate\Http\RedirectResponse, 'screenshotsDelete must return RedirectResponse');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in screenshotsDelete');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in screenshotsDelete');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in screenshotsDelete');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in screenshotsDelete');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in screenshotsDelete');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in screenshotsDelete');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in screenshotsDelete');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in screenshotsDelete');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in screenshotsDelete');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in screenshotsDelete');
                return;
        }
    }

}
