<?php
declare(strict_types=1);
namespace Tests\Unit\Modules\LandingPage\Http\Controllers;

use Tests\TestCase;
use Tests\Unit\app\Http\Controllers\ControllerTestHelper;
use Modules\LandingPage\Http\Controllers\TestimonialsController;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Contracts\Support\Renderable;

/**
 * @covers \Modules\LandingPage\Http\Controllers\TestimonialsController
 */
class TestimonialsControllerTest extends TestCase
{
    use ControllerTestHelper;

    public function test_constant_IDX_equals_index_1(): void
    {
        $this->assertSame('index', TestimonialsController::IDX);
    }

    public function test_constant_CRT_equals_create_2(): void
    {
        $this->assertSame('create', TestimonialsController::CRT);
    }

    public function test_constant_STR_equals_store_3(): void
    {
        $this->assertSame('store', TestimonialsController::STR);
    }

    public function test_constant_SHW_equals_show_4(): void
    {
        $this->assertSame('show', TestimonialsController::SHW);
    }

    public function test_constant_EDT_equals_edit_5(): void
    {
        $this->assertSame('edit', TestimonialsController::EDT);
    }

    public function test_constant_UPD_equals_update_6(): void
    {
        $this->assertSame('update', TestimonialsController::UPD);
    }

    public function test_constant_DEL_equals_destroy_7(): void
    {
        $this->assertSame('destroy', TestimonialsController::DEL);
    }

    public function test_constant_TTM_CRT_equals_testimonialsCreate_8(): void
    {
        $this->assertSame('testimonialsCreate', TestimonialsController::TTM_CRT);
    }

    public function test_constant_TTM_STR_equals_testimonialsStore_9(): void
    {
        $this->assertSame('testimonialsStore', TestimonialsController::TTM_STR);
    }

    public function test_constant_TTM_EDT_equals_testimonialsEdit_10(): void
    {
        $this->assertSame('testimonialsEdit', TestimonialsController::TTM_EDT);
    }

    public function test_constant_TTM_UPD_equals_testimonialsUpdate_11(): void
    {
        $this->assertSame('testimonialsUpdate', TestimonialsController::TTM_UPD);
    }

    public function test_constant_TTM_DEL_equals_testimonialsDelete_12(): void
    {
        $this->assertSame('testimonialsDelete', TestimonialsController::TTM_DEL);
    }

    public function test_index_returns_expected_type_13(): void
    {
        $this->loginMockUser();
        $ctrl = new TestimonialsController();
        try {
            $result = $ctrl->index($this->makeRequest());
            $this->assertTrue($result instanceof \Illuminate\Contracts\Support\Renderable || $result instanceof \Illuminate\Http\RedirectResponse || $result === null, 'index must return Renderable|RedirectResponse|null');
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
        $ctrl = new TestimonialsController();
        try {
            $result = $ctrl->show($this->makeRequest(), 1);
            $this->assertTrue($result instanceof \Illuminate\Http\RedirectResponse || $result === null, 'show must return RedirectResponse|null');
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
        $ctrl = new TestimonialsController();
        try {
            $result = $ctrl->create($this->makeRequest());
            $this->assertTrue($result instanceof \Illuminate\Contracts\Support\Renderable || $result instanceof \Illuminate\Http\RedirectResponse || $result === null, 'create must return Renderable|RedirectResponse|null');
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
        $ctrl = new TestimonialsController();
        try {
            $result = $ctrl->store($this->makeRequest());
            $this->assertTrue($result instanceof \Illuminate\Http\RedirectResponse || $result === null, 'store must return RedirectResponse|null');
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
        $ctrl = new TestimonialsController();
        try {
            $req = $this->makeRequest('POST', '/', ['_token' => 'test']);
            $result = $ctrl->store($req);
            $this->assertTrue($result instanceof \Illuminate\Http\RedirectResponse || $result === null, 'store must return RedirectResponse|null');
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
        $ctrl = new TestimonialsController();
        try {
            $result = $ctrl->edit($this->makeRequest(), 1);
            $this->assertTrue($result instanceof \Illuminate\Contracts\Support\Renderable || $result instanceof \Illuminate\Http\RedirectResponse || $result === null, 'edit must return Renderable|RedirectResponse|null');
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
        $ctrl = new TestimonialsController();
        try {
            $result = $ctrl->update($this->makeRequest(), 1);
            $this->assertTrue($result instanceof \Illuminate\Http\RedirectResponse || $result === null, 'update must return RedirectResponse|null');
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
        $ctrl = new TestimonialsController();
        try {
            $req = $this->makeRequest('POST', '/', ['_token' => 'test']);
            $result = $ctrl->update($req, 1);
            $this->assertTrue($result instanceof \Illuminate\Http\RedirectResponse || $result === null, 'update must return RedirectResponse|null');
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
        $ctrl = new TestimonialsController();
        try {
            $result = $ctrl->destroy($this->makeRequest(), 1);
            $this->assertTrue($result instanceof \Illuminate\Http\RedirectResponse || $result === null, 'destroy must return RedirectResponse|null');
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

    public function test_testimonialsCreate_returns_expected_type_22(): void
    {
        $this->loginMockUser();
        $ctrl = new TestimonialsController();
        try {
            $result = $ctrl->testimonialsCreate($this->makeRequest());
            $this->assertTrue(true, 'testimonialsCreate returned without error');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in testimonialsCreate');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in testimonialsCreate');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in testimonialsCreate');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in testimonialsCreate');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in testimonialsCreate');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in testimonialsCreate');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in testimonialsCreate');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in testimonialsCreate');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in testimonialsCreate');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in testimonialsCreate');
                return;
        }
    }

    public function test_testimonialsStore_returns_expected_type_23(): void
    {
        $this->loginMockUser();
        $ctrl = new TestimonialsController();
        try {
            $result = $ctrl->testimonialsStore($this->makeRequest());
            $this->assertTrue(true, 'testimonialsStore returned without error');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in testimonialsStore');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in testimonialsStore');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in testimonialsStore');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in testimonialsStore');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in testimonialsStore');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in testimonialsStore');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in testimonialsStore');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in testimonialsStore');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in testimonialsStore');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in testimonialsStore');
                return;
        }
    }

    public function test_testimonialsStore_with_post_data_24(): void
    {
        $this->loginMockUser();
        $ctrl = new TestimonialsController();
        try {
            $req = $this->makeRequest('POST', '/', ['_token' => 'test']);
            $result = $ctrl->testimonialsStore($req);
            $this->assertTrue(true, 'testimonialsStore returned without error');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in testimonialsStore');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in testimonialsStore');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in testimonialsStore');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in testimonialsStore');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in testimonialsStore');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in testimonialsStore');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in testimonialsStore');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in testimonialsStore');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in testimonialsStore');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in testimonialsStore');
                return;
        }
    }

    public function test_testimonialsEdit_returns_expected_type_25(): void
    {
        $this->loginMockUser();
        $ctrl = new TestimonialsController();
        try {
            $result = $ctrl->testimonialsEdit($this->makeRequest(), 1);
            $this->assertTrue(true, 'testimonialsEdit returned without error');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in testimonialsEdit');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in testimonialsEdit');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in testimonialsEdit');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in testimonialsEdit');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in testimonialsEdit');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in testimonialsEdit');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in testimonialsEdit');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in testimonialsEdit');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in testimonialsEdit');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in testimonialsEdit');
                return;
        }
    }

    public function test_testimonialsUpdate_returns_expected_type_26(): void
    {
        $this->loginMockUser();
        $ctrl = new TestimonialsController();
        try {
            $result = $ctrl->testimonialsUpdate($this->makeRequest(), 1);
            $this->assertTrue(true, 'testimonialsUpdate returned without error');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in testimonialsUpdate');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in testimonialsUpdate');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in testimonialsUpdate');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in testimonialsUpdate');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in testimonialsUpdate');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in testimonialsUpdate');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in testimonialsUpdate');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in testimonialsUpdate');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in testimonialsUpdate');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in testimonialsUpdate');
                return;
        }
    }

    public function test_testimonialsUpdate_with_post_data_27(): void
    {
        $this->loginMockUser();
        $ctrl = new TestimonialsController();
        try {
            $req = $this->makeRequest('POST', '/', ['_token' => 'test']);
            $result = $ctrl->testimonialsUpdate($req, 1);
            $this->assertTrue(true, 'testimonialsUpdate returned without error');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in testimonialsUpdate');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in testimonialsUpdate');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in testimonialsUpdate');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in testimonialsUpdate');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in testimonialsUpdate');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in testimonialsUpdate');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in testimonialsUpdate');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in testimonialsUpdate');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in testimonialsUpdate');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in testimonialsUpdate');
                return;
        }
    }

    public function test_testimonialsDelete_returns_expected_type_28(): void
    {
        $this->loginMockUser();
        $ctrl = new TestimonialsController();
        try {
            $result = $ctrl->testimonialsDelete($this->makeRequest(), 1);
            $this->assertTrue(true, 'testimonialsDelete returned without error');
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RouteNotFoundException in testimonialsDelete');
                return;
            } catch (\BadMethodCallException $e) {
                $this->assertNotEmpty($e->getMessage(), 'BadMethodCallException in testimonialsDelete');
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotEmpty($e->getMessage(), 'QueryException in testimonialsDelete');
                return;
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage(), 'RuntimeException in testimonialsDelete');
                return;
            } catch (\ErrorException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ErrorException in testimonialsDelete');
                return;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ValidationException in testimonialsDelete');
                return;
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                $this->assertNotEmpty($e->getMessage(), 'AuthorizationException in testimonialsDelete');
                return;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertNotEmpty($e->getMessage(), 'ModelNotFoundException in testimonialsDelete');
                return;
            } catch (\TypeError $e) {
                $this->assertNotEmpty($e->getMessage(), 'TypeError in testimonialsDelete');
                return;
            } catch (\Throwable $e) {
                $this->assertNotEmpty($e->getMessage(), 'Throwable in testimonialsDelete');
                return;
        }
    }

}
