<?php

namespace App\Http\Controllers;

use App\Config\Constants\{DatabaseConstants, PermissionsConstants, ViewsConstants};
use App\Models\Template;
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\{DB, Log, Validator, View as ViewFacade};
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Orhanerday\OpenAi\OpenAi;

final class AiTemplateController extends Controller
{
  use ChecksLogin, ChecksPermissions;

  private const SINGULAR = 'template';
  private const REDIRECT_INDEX = '/';

  public function create(string $moduleName): View|RedirectResponse
  {
    $cls = __CLASS__;
    $fn = __FUNCTION__;
    $action = "$cls::$fn";
    $view = self::SINGULAR . '.generateAi';

    return $this->measureProfile($action, function () use ($moduleName, $action, $view) {
      if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
      $guard = self::guard(request(), PermissionsConstants::MNG_AI_TPL, self::REDIRECT_INDEX);
      if ($guard !== true) return $guard;

      $templates = Template::where('module', $moduleName)->get();

      if (!ViewFacade::exists($view)) {
        return defaultUndefinedException(request(), new \RuntimeException('View not found'), $action);
      }

      return view($view, compact(DatabaseConstants::TABLE_TEMPLATES));
    });
  }

  public const GET_KW = 'getKeywords';
  public function getKeywords(Request $req, int $id): JsonResponse|RedirectResponse
  {
    $cls = __CLASS__;
    $fn = __FUNCTION__;
    $action = "$cls::$fn";

    return $this->measureProfile($action, function () use ($req, $id) {
      if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
      $guard = self::guard($req, PermissionsConstants::MNG_AI_TPL, self::REDIRECT_INDEX);
      if ($guard !== true) return $guard;

      $template = Template::find($id);
      if (!$template) {
        return response()->json(['success' => false, 'message' => __('Template not found.')], 404);
      }

      $fieldJson = json_decode($template->fieldJson);
      if (!$fieldJson || !isset($fieldJson->field) || !is_array($fieldJson->field)) {
        return response()->json(['success' => false, 'message' => __('Invalid template field schema.')], 422);
      }

      $html = '';
      foreach ($fieldJson->field as $field) {
        $label = e($field->label ?? '');
        $name  = e($field->fieldName ?? '');
        $ph    = e($field->placeholder ?? '');
        if (($field->fieldType ?? '') === 'textBox') {
          $html .= '<div class="form-group col-md-12"><label class="form-label">' . $label . '</label><input type="text" class="form-control" name="' . $name . '" placeholder="' . $ph . '" required></div>';
        } else {
          $html .= '<div class="form-group col-md-12"><label class="form-label">' . $label . '</label><textarea rows="3" class="form-control" name="' . $name . '" placeholder="' . $ph . '" required></textarea></div>';
        }
      }

      return response()->json([
        'success' => true,
        'tone' => (bool)($template->isTone ?? false),
        self::SINGULAR => $html,
      ]);
    });
  }

  public const AIG = 'aiGenerate';
  public function aiGenerate(Request $req): JsonResponse|RedirectResponse
  {
    $cls = __CLASS__;
    $fn = __FUNCTION__;
    $action = "$cls::$fn";

    return $this->measureProfile($action, function () use ($req, $action) {
      if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
      $guard = self::guard($req, PermissionsConstants::MNG_AI_TPL, self::REDIRECT_INDEX);
      if ($guard !== true) return $guard;

      if (!$req->ajax()) {
        return response()->json(['error' => 'Invalid request'], 400);
      }

      $keyRecord = DB::table(DatabaseConstants::TABLE_SETTINGS)->where('name', 'chatGptKey')->first();
      if (!$keyRecord || empty($keyRecord->value)) {
        return response()->json([
          'status' => 'error',
          'message' => __('Please set proper configuration for Api Key'),
        ], 500);
      }

      try {
        $template = Template::find($req->templateName);
        if (!$template) {
          return response()->json(['error' => __('Template not found')], 404);
        }

        $fieldSchema = json_decode($template->fieldJson);
        if (!$fieldSchema || !isset($fieldSchema->field) || !is_array($fieldSchema->field)) {
          return response()->json(['error' => __('Invalid template field schema')], 422);
        }

        foreach ($fieldSchema->field as $field) {
          Validator::make(
            [$field->fieldName => $req->input($field->fieldName)],
            [$field->fieldName => 'required|string']
          )->validate();
        }

        $prompt = (string)$template->prompt;
        foreach ($fieldSchema->field as $field) {
          $tag = '##' . $field->fieldName . '##';
          $value = (string)$req->input($field->fieldName, '');
          $prompt = str_contains($prompt, $tag) ? str_replace($tag, $value, $prompt) : $prompt;
        }
        if ($template->isTone ?? false) {
          $prompt = str_replace('##toneLanguage##', (string)$req->input('tone', ''), $prompt);
        }

        $langText = 'Provide response in ' . ($req->input('language', 'English')) . ' language.';
        $temperature = (float)$req->input('aiCreativity', 0.7);
        $maxTokens = max(1, (int)$req->input('resultLength', 256));
        $n = max(1, min(5, (int)$req->input('numOfResult', 1)));

        $ai = new OpenAi($keyRecord->value);
        $raw = $ai->completion([
          'model' => 'text-davinci-003',
          'prompt' => $prompt . ' ' . $langText,
          'temperature' => $temperature,
          'max_tokens' => $maxTokens,
          'n' => $n,
        ]);
        $response = json_decode($raw, true);

        if (!is_array($response) || !isset($response['choices'])) {
          Log::warning($action . ' invalid response', ['raw' => $raw]);
          throw new \RuntimeException('Invalid OpenAI response');
        }

        $text = '';
        $i = 1;
        foreach ($response['choices'] as $choice) {
          $chunk = ltrim((string)($choice['text'] ?? ''));
          if ($chunk !== '') {
            $text .= ($n > 1 ? ($i++) . '. ' : '') . $chunk . "\n\n";
          }
        }

        return response()->json(['text' => trim($text)]);
      } catch (ValidationException $ve) {
        $msg = collect($ve->errors())->flatten()->first() ?? __('Validation failed');
        return response()->json(['error' => $msg], 422);
      } catch (\Throwable $e) {
        Log::error($action . ' failed to generate AI text: ' . $e->getMessage());
        return response()->json(['error' => __('AI generation failed')], 500);
      }
    });
  }

  public function grammar(string $moduleName): View|RedirectResponse
  {
    $cls = __CLASS__;
    $fn = __FUNCTION__;
    $action = "$cls::$fn";
    $view = self::SINGULAR . '.grammarAi';

    return $this->measureProfile($action, function () use ($moduleName, $view, $action) {
      if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
      $guard = self::guard(request(), PermissionsConstants::MNG_AI_TPL, self::REDIRECT_INDEX);
      if ($guard !== true) return $guard;

      $template = Template::where('module', $moduleName)->first();

      if (!ViewFacade::exists($view)) {
        return defaultUndefinedException(request(), new \RuntimeException('View not found'), $action);
      }

      return view($view, compact('template'));
    });
  }

  public const GM_P = 'grammarProcess';
  public function grammarProcess(Request $req): JsonResponse|RedirectResponse
  {
    $cls = __CLASS__;
    $fn = __FUNCTION__;
    $action = "$cls::$fn";

    return $this->measureProfile($action, function () use ($req, $action) {
      if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;
      $guard = self::guard($req, PermissionsConstants::MNG_AI_TPL, self::REDIRECT_INDEX);
      if ($guard !== true) return $guard;

      if (!$req->ajax()) {
        return response()->json(['error' => 'Invalid request'], 400);
      }

      $keyRecord = DB::table(DatabaseConstants::TABLE_SETTINGS)->where('name', 'chatGptKey')->first();
      if (!$keyRecord || empty($keyRecord->value)) {
        return response()->json([
          'status' => 'error',
          'message' => __('Please set proper configuration for Api Key'),
        ], 500);
      }

      try {
        $desc = (string)$req->input('description', '');
        if ($desc === '') {
          return response()->json(['error' => __('Description is required')], 422);
        }

        $prompt = "Please correct grammar and spelling mistakes in this text, preserving meaning and tone:\n\n" . $desc;

        $ai = new OpenAi($keyRecord->value);
        $raw = $ai->completion([
          'model' => 'text-davinci-003',
          'prompt' => $prompt,
          'temperature' => 0.2,
          'max_tokens' => max(64, min(2048, (int)ceil(strlen($desc) / 3.5))),
          'n' => 1,
        ]);
        $response = json_decode($raw, true);

        if (!is_array($response) || !isset($response['choices'][0]['text'])) {
          Log::warning($action . ' invalid response', ['raw' => $raw]);
          throw new \RuntimeException('Invalid OpenAI response');
        }

        return response()->json([
          'text' => ltrim((string)$response['choices'][0]['text']),
        ]);
      } catch (\Throwable $e) {
        Log::error($action . ' grammar check failed: ' . $e->getMessage());
        return response()->json(['error' => __('Grammar check failed')], 500);
      }
    });
  }
}
