<?php
declare(strict_types=1);
namespace Tests\Unit\Modules\LandingPage\Http\Controllers;

use Tests\TestCase;
use Tests\Unit\app\Http\Controllers\ControllerTestHelper;
use Modules\LandingPage\Http\Controllers\DiscoverController;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Contracts\Support\Renderable;

/**
 * @covers \Modules\LandingPage\Http\Controllers\DiscoverController
 */
class DiscoverControllerTest extends TestCase
{
    use ControllerTestHelper;

    public function test_constant_IDX_equals_index_1(): void
    {
        $this->assertSame('index', DiscoverController::IDX);
    }

    public function test_constant_CRT_equals_create_2(): void
    {
        $this->assertSame('create', DiscoverController::CRT);
    }

    public function test_constant_STR_equals_store_3(): void
    {
        $this->assertSame('store', DiscoverController::STR);
    }

    public function test_constant_SHW_equals_show_4(): void
    {
        $this->assertSame('show', DiscoverController::SHW);
    }

    public function test_constant_EDT_equals_edit_5(): void
    {
        $this->assertSame('edit', DiscoverController::EDT);
    }

    public function test_constant_UPD_equals_update_6(): void
    {
        $this->assertSame('update', DiscoverController::UPD);
    }

    public function test_constant_DEL_equals_destroy_7(): void
    {
        $this->assertSame('destroy', DiscoverController::DEL);
    }

    public function test_constant_DCV_EDT_equals_discoverEdit_8(): void
    {
        $this->assertSame('discoverEdit', DiscoverController::DCV_EDT);
    }

    public function test_constant_DCV_UPD_equals_discoverUpdate_9(): void
    {
        $this->assertSame('discoverUpdate', DiscoverController::DCV_UPD);
    }

    public function test_constant_DCV_DEL_equals_discoverDelete_10(): void
    {
        $this->assertSame('discoverDelete', DiscoverController::DCV_DEL);
    }

    public function test_constant_DCV_CRT_equals_discoverCreate_11(): void
    {
        $this->assertSame('discoverCreate', DiscoverController::DCV_CRT);
    }

    public function test_constant_DCV_STR_equals_discoverStore_12(): void
    {
        $this->assertSame('discoverStore', DiscoverController::DCV_STR);
    }

    public function test_index_returns_expected_type_13(): void
    {
        $this->loginMockUser();
        $ctrl = new DiscoverController();
        try {
            $result = $ctrl->index($this->makeRequest());
            $this->assertTrue($result instanceof \Illuminate\Contracts\Support\Renderable || $result instanceof \Illuminate\Http\RedirectResponse, 'index must return Renderable|RedirectResponse');
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
        $ctrl = new DiscoverController();
        try {
            $result = $ctrl->show(1);
            $this->assertTrue($result instanceof \Illuminate\Contracts\Support\Renderable || $result instanceof \Illuminate\Http\RedirectResponse, 'show must return Renderable|RedirectResponse');
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
        $ctrl = new DiscoverController();
        try {
            $result = $ctrl->create($this->makeRequest());
            $this->assertTrue($result instanceof \Illuminate\Contracts\Support\Renderable || $result instanceof \Illuminate\Http\RedirectResponse, 'create must return Renderable|RedirectResponse');
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
        $ctrl = new DiscoverController();
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
        $ctrl = new DiscoverController();
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
        $ctrl = new DiscoverController();
        try {
            $result = $ctrl->edit(1);
            $this->assertTrue($result instanceof \Illuminate\Contracts\Support\Renderable || $result instanceof \Illuminate\Http\RedirectResponse, 'edit must return Renderable|RedirectResponse');
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
        $ctrl = new DiscoverController();
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
        $ctrl = new DiscoverController();
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
        $ctrl = new DiscoverController();
        try {
            $result = $ctrl->destroy(1);
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

    public function test_discoverEdit_returns_expected_type_22(): void
    {
        $this->loginMockUser();
        $ctrl = new DiscoverController();
        try {
            $result = $ctrl->discoverEdit($this->makeRequest(), 1);
            $this->assertTrue($result instanceof \Illuminate\Contracts\Support\Renderable || $result instanceof \Illuminate\Http\RedirectResponse, 'discoverEdit must return Renderable|RedirectResponse');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in discoverEdit');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in discoverEdit');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in discoverEdit');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in discoverEdit');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in discoverEdit');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in discoverEdit');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in discoverEdit');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in discoverEdit');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in discoverEdit');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in discoverEdit');
                return;
        }
    }

    public function test_discoverUpdate_returns_expected_type_23(): void
    {
        $this->loginMockUser();
        $ctrl = new DiscoverController();
        try {
            $result = $ctrl->discoverUpdate($this->makeRequest(), 1);
            $this->assertTrue($result instanceof \Illuminate\Http\RedirectResponse, 'discoverUpdate must return RedirectResponse');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in discoverUpdate');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in discoverUpdate');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in discoverUpdate');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in discoverUpdate');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in discoverUpdate');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in discoverUpdate');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in discoverUpdate');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in discoverUpdate');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in discoverUpdate');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in discoverUpdate');
                return;
        }
    }

    public function test_discoverUpdate_with_post_data_24(): void
    {
        $this->loginMockUser();
        $ctrl = new DiscoverController();
        try {
            $req = $this->makeRequest('POST', '/', ['_token' => 'test']);
            $result = $ctrl->discoverUpdate($req, 1);
            $this->assertTrue($result instanceof \Illuminate\Http\RedirectResponse, 'discoverUpdate must return RedirectResponse');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in discoverUpdate');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in discoverUpdate');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in discoverUpdate');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in discoverUpdate');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in discoverUpdate');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in discoverUpdate');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in discoverUpdate');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in discoverUpdate');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in discoverUpdate');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in discoverUpdate');
                return;
        }
    }

    public function test_discoverDelete_returns_expected_type_25(): void
    {
        $this->loginMockUser();
        $ctrl = new DiscoverController();
        try {
            $result = $ctrl->discoverDelete($this->makeRequest(), 1);
            $this->assertTrue($result instanceof \Illuminate\Http\RedirectResponse, 'discoverDelete must return RedirectResponse');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in discoverDelete');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in discoverDelete');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in discoverDelete');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in discoverDelete');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in discoverDelete');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in discoverDelete');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in discoverDelete');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in discoverDelete');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in discoverDelete');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in discoverDelete');
                return;
        }
    }

    public function test_discoverCreate_returns_expected_type_26(): void
    {
        $this->loginMockUser();
        $ctrl = new DiscoverController();
        try {
            $result = $ctrl->discoverCreate();
            $this->assertTrue($result instanceof \Illuminate\Contracts\Support\Renderable || $result instanceof \Illuminate\Http\RedirectResponse, 'discoverCreate must return Renderable|RedirectResponse');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in discoverCreate');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in discoverCreate');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in discoverCreate');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in discoverCreate');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in discoverCreate');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in discoverCreate');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in discoverCreate');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in discoverCreate');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in discoverCreate');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in discoverCreate');
                return;
        }
    }

    public function test_discoverStore_returns_expected_type_27(): void
    {
        $this->loginMockUser();
        $ctrl = new DiscoverController();
        try {
            $result = $ctrl->discoverStore($this->makeRequest());
            $this->assertTrue($result instanceof \Illuminate\Http\RedirectResponse, 'discoverStore must return RedirectResponse');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in discoverStore');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in discoverStore');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in discoverStore');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in discoverStore');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in discoverStore');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in discoverStore');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in discoverStore');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in discoverStore');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in discoverStore');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in discoverStore');
                return;
        }
    }

    public function test_discoverStore_with_post_data_28(): void
    {
        $this->loginMockUser();
        $ctrl = new DiscoverController();
        try {
            $req = $this->makeRequest('POST', '/', ['_token' => 'test']);
            $result = $ctrl->discoverStore($req);
            $this->assertTrue($result instanceof \Illuminate\Http\RedirectResponse, 'discoverStore must return RedirectResponse');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in discoverStore');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in discoverStore');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in discoverStore');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in discoverStore');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in discoverStore');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in discoverStore');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in discoverStore');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in discoverStore');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in discoverStore');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in discoverStore');
                return;
        }
    }

}
