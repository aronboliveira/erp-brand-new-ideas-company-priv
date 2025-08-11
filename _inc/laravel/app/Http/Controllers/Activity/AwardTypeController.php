<?php

namespace App\Http\Controllers;

use App\Config\Constants\DatabaseConstants;
use App\Models\AwardType;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AwardTypeController extends Controller
{

  public function index(Request $request): mixed
  {
    try {
      self::_setAuth($request, 'manage award type');
      $awardTypes = AwardType::query()
        ->where(DatabaseConstants::TABLE_CREATOR, $request->user()->creatorId())
        ->get();
      return view(
        Str::snake(
          Str::replaceLast(
            'Controller',
            '',
            class_basename(__CLASS__)
          )
        ) . '.index',
        ['awardTypes' => $awardTypes]
      );
    } catch (AuthorizationException $e) {
      return defaultPermissionDenial(
        $request,
        $e,
        __CLASS__ . '::' . __FUNCTION__
      );
    } catch (\Throwable $e) {
      return defaultUndefinedException(
        $request,
        $e,
        __CLASS__ . '::' . __FUNCTION__
      );
    }
  }

  public function create(Request $request): mixed
  {
    try {
      self::_setAuth($request, 'create award type');
      return view(
        Str::snake(
          Str::replaceLast(
            'Controller',
            '',
            class_basename(__CLASS__)
          )
        ) . '.create'
      );
    } catch (AuthorizationException $e) {
      return defaultPermissionDenial(
        $request,
        $e,
        __CLASS__ . '::' . __FUNCTION__
      );
    } catch (\Throwable $e) {
      return defaultUndefinedException(
        $request,
        $e,
        __CLASS__ . '::' . __FUNCTION__
      );
    }
  }

  public function store(Request $request): mixed
  {
    try {
      self::_setAuth($request, 'create award type');
      $validator = Validator::make(
        $request->all(),
        ['name' => 'required|max:20']
      );
      if ($validator->fails())
        return redirect()
          ->back()
          ->with('error', $validator->errors()->first());
      $awardType = new AwardType();
      $awardType->name = $request->input('name');
      $awardType->created_by = $request->user()->creatorId();
      $awardType->save();
      $resource = Str::snake(
        Str::replaceLast(
          'Controller',
          '',
          class_basename(__CLASS__)
        )
      );
      return redirect()
        ->route($resource . '.index')
        ->with(
          'success',
          __(
            ':resource successfully created.',
            [
              'resource' => Str::of($resource)
                ->replace('_', ' ')
                ->title()
            ]
          )
        );
    } catch (AuthorizationException $e) {
      return defaultPermissionDenial(
        $request,
        $e,
        __CLASS__ . '::' . __FUNCTION__
      );
    } catch (\Throwable $e) {
      return defaultUndefinedException(
        $request,
        $e,
        __CLASS__ . '::' . __FUNCTION__
      );
    }
  }

  public function show(Request $request, AwardType $awardType): mixed
  {
    try {
      $resource = Str::snake(
        Str::replaceLast(
          'Controller',
          '',
          class_basename(__CLASS__)
        )
      );
      return redirect()->route($resource . '.index');
    } catch (\Throwable $e) {
      return defaultUndefinedException(
        $request,
        $e,
        __CLASS__ . '::' . __FUNCTION__
      );
    }
  }

  public function edit(Request $request, AwardType $awardType): mixed
  {
    try {
      self::_setAuth($request, 'edit award type');
      if (
        $awardType->created_by
        !== $request->user()->creatorId()
      ) {
        throw new AuthorizationException;
      }
      return view(
        Str::snake(
          Str::replaceLast(
            'Controller',
            '',
            class_basename(__CLASS__)
          )
        ) . '.' . __FUNCTION__,
        ['awardType' => $awardType]
      );
    } catch (AuthorizationException $e) {
      return defaultPermissionDenial(
        $request,
        $e,
        __CLASS__ . '::' . __FUNCTION__
      );
    } catch (\Throwable $e) {
      return defaultUndefinedException(
        $request,
        $e,
        __CLASS__ . '::' . __FUNCTION__
      );
    }
  }

  public function update(Request $request, AwardType $awardType): mixed
  {
    try {
      self::_setAuth($request, 'edit award type');
      if (
        $awardType->created_by
        !== $request->user()->creatorId()
      ) {
        throw new AuthorizationException;
      }
      $validator = Validator::make(
        $request->all(),
        ['name' => 'required|max:20']
      );
      if ($validator->fails()) {
        return redirect()
          ->back()
          ->with('error', $validator->errors()->first());
      }
      $awardType->name = $request->input('name');
      $awardType->save();
      $resource = Str::snake(
        Str::replaceLast(
          'Controller',
          '',
          class_basename(__CLASS__)
        )
      );
      return redirect()
        ->route($resource . '.index')
        ->with(
          'success',
          __(
            ':resource successfully updated.',
            [
              'resource' => Str::of($resource)
                ->replace('_', ' ')
                ->title()
            ]
          )
        );
    } catch (AuthorizationException $e) {
      return defaultPermissionDenial(
        $request,
        $e,
        __CLASS__ . '::' . __FUNCTION__
      );
    } catch (\Throwable $e) {
      return defaultUndefinedException(
        $request,
        $e,
        __CLASS__ . '::' . __FUNCTION__
      );
    }
  }

  public function destroy(Request $request, AwardType $awardType): mixed
  {
    try {
      self::_setAuth($request, 'delete award type');
      if (
        $awardType->created_by
        !== $request->user()->creatorId()
      ) {
        throw new AuthorizationException;
      }
      $awardType->delete();
      $resource = Str::snake(
        Str::replaceLast(
          'Controller',
          '',
          class_basename(__CLASS__)
        )
      );
      return redirect()
        ->route($resource . '.index')
        ->with(
          'success',
          __(
            ':resource successfully deleted.',
            [
              'resource' => Str::of($resource)
                ->replace('_', ' ')
                ->title()
            ]
          )
        );
    } catch (AuthorizationException $e) {
      return defaultPermissionDenial(
        $request,
        $e,
        __CLASS__ . '::' . __FUNCTION__
      );
    } catch (\Throwable $e) {
      return defaultUndefinedException(
        $request,
        $e,
        __CLASS__ . '::' . __FUNCTION__
      );
    }
  }

  protected static function _setAuth(Request $request, string $permission, string $suffix = ''): void
  {
    $perm = $permission . ($suffix ? " {$suffix}" : '');
    if (!$request->user()->can($perm)) {
      throw new AuthorizationException;
    }
  }
}
