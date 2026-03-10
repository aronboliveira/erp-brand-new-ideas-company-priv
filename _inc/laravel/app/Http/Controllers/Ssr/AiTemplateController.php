<?php

namespace App\Http\Controllers\Ssr;

use App\Config\Constants\{
  DatabaseConstants as DC,
  PermissionsConstants as PMC
};
use App\Http\Controllers\Abstracts\Controller;
use App\Models\Template;
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\{DB, Log, Validator, View as ViewFacade};
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Orhanerday\OpenAi\OpenAi;

use function App\Http\Controllers\Helpers\defaultUndefinedException;
final class AiTemplateController extends Controller
{
  use ChecksLogin, ChecksPermissions;

  private const SINGULAR = 'template';
  private const REDIRECT_INDEX = '/';
  private const OPENAI_MODEL = 'text-davinci-003';
  private const CHATGPT_KEY_NAME = 'chatGptKey';

  private function fetchApiKey(string $action): ?object
  {
    $file = __FILE__;
    try {
      $keyRecord = DB::table(DC::TABLE_SETTINGS)
        ->where('name', self::CHATGPT_KEY_NAME)
        ->first();
      if (empty($keyRecord) || !is_object($keyRecord)) {
        Log::warning("{$action} API key record not found", [
          'file' => $file,
          'class' => __CLASS__,
          'method' => __FUNCTION__
        ]);
        return null;
      }
      $value = $keyRecord->value ?? null;
      if (empty($value) || !is_string($value)) {
        Log::warning("{$action} API key value is empty or invalid", [
          'file' => $file,
          'class' => __CLASS__,
          'method' => __FUNCTION__
        ]);
        return null;
      }
      return $keyRecord;
    } catch (\Throwable $e) {
      Log::error("{$action} failed to fetch API key", [
        'file' => $file,
        'class' => __CLASS__,
        'method' => __FUNCTION__,
        'error_class' => get_class($e),
        'message' => $e->getMessage()
      ]);
      return null;
    }
  }

  private function executeCompletion(
    string $action,
    string $apiKey,
    string $prompt,
    float $temperature,
    int $maxTokens,
    int $n = 1
  ): ?array {
    $file = __FILE__;
    $raw = null;
    try {
      $ai = new OpenAi($apiKey);
      $raw = $ai->completion([
        'model' => self::OPENAI_MODEL,
        'prompt' => $prompt,
        'temperature' => $temperature,
        'max_tokens' => $maxTokens,
        'n' => $n,
      ]);
      if (empty($raw) || !is_string($raw)) {
        Log::warning("{$action} OpenAI returned empty or non-string response", [
          'file' => $file,
          'class' => __CLASS__,
          'method' => __FUNCTION__
        ]);
        return null;
      }
      $response = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
      if (!is_array($response)) {
        Log::warning("{$action} OpenAI response is not an array", [
          'file' => $file,
          'class' => __CLASS__,
          'method' => __FUNCTION__,
          'raw' => mb_substr((string)$raw, 0, 500)
        ]);
        return null;
      }
      return $response;
    } catch (\JsonException $je) {
      Log::error("{$action} JSON decode failed", [
        'file' => $file,
        'class' => __CLASS__,
        'method' => __FUNCTION__,
        'error_class' => get_class($je),
        'message' => $je->getMessage(),
        'raw_snippet' => mb_substr((string)$raw, 0, 500)
      ]);
      return null;
    } catch (\Throwable $e) {
      Log::error("{$action} OpenAI completion failed", [
        'file' => $file,
        'class' => __CLASS__,
        'method' => __FUNCTION__,
        'error_class' => get_class($e),
        'message' => $e->getMessage()
      ]);
      return null;
    }
  }

  private function decodeFieldJson(string $action, ?string $jsonStr): ?object
  {
    $file = __FILE__;
    if (empty($jsonStr) || !is_string($jsonStr)) {
      Log::warning("{$action} fieldJson is empty or not a string", [
        'file' => $file,
        'class' => __CLASS__,
        'method' => __FUNCTION__
      ]);
      return null;
    }
    try {
      $decoded = json_decode($jsonStr, false, 512, JSON_THROW_ON_ERROR);
      if (!is_object($decoded)) {
        Log::warning("{$action} fieldJson decoded to non-object", [
          'file' => $file,
          'class' => __CLASS__,
          'method' => __FUNCTION__
        ]);
        return null;
      }
      if (!isset($decoded->field) || !is_array($decoded->field)) {
        Log::warning("{$action} fieldJson missing 'field' array", [
          'file' => $file,
          'class' => __CLASS__,
          'method' => __FUNCTION__
        ]);
        return null;
      }
      return $decoded;
    } catch (\JsonException $je) {
      Log::error("{$action} fieldJson decode failed", [
        'file' => $file,
        'class' => __CLASS__,
        'method' => __FUNCTION__,
        'error_class' => get_class($je),
        'message' => $je->getMessage()
      ]);
      return null;
    }
  }

  public function create(string $moduleName): View|RedirectResponse
  {
    $action = __CLASS__ . '::' . __FUNCTION__;
    $file = __FILE__;
    $view = self::SINGULAR . '.generateAi';
    return $this->measureProfile($action, function () use (
      $moduleName,
      $action,
      $view,
      $file
    ) {
      try {
        $userOrRedirect = self::_checkLogin();
        if ($userOrRedirect instanceof RedirectResponse)
          return $userOrRedirect;
        $guard = self::guard(request(), PMC::MNG_AI_TPL, self::REDIRECT_INDEX);
        if ($guard !== true)
          return $guard;
        $templates = Template::where('module', $moduleName)->get();
        if (!ViewFacade::exists($view)) {
          Log::error("{$action} View not found", [
            'file' => $file,
            'class' => __CLASS__,
            'view' => $view
          ]);
          return defaultUndefinedException(
            request(),
            new \RuntimeException("View '{$view}' not found"),
            $action
          );
        }
        return view($view, compact(DC::TABLE_TMP));
      } catch (\RuntimeException $re) {
        Log::error("{$action} RuntimeException", [
          'file' => $file,
          'class' => __CLASS__,
          'error_class' => get_class($re),
          'message' => $re->getMessage()
        ]);
        return defaultUndefinedException(request(), $re, $action);
      } catch (\Throwable $e) {
        Log::error("{$action} Unexpected error", [
          'file' => $file,
          'class' => __CLASS__,
          'error_class' => get_class($e),
          'message' => $e->getMessage()
        ]);
        return defaultUndefinedException(request(), $e, $action);
      }
    });
  }

  public const GET_KW = 'getKeywords';
  public function getKeywords(Request $req, int $id): JsonResponse|RedirectResponse
  {
    $action = __CLASS__ . '::' . __FUNCTION__;
    $file = __FILE__;
    return $this->measureProfile($action, function () use ($req, $id, $action, $file) {
      try {
        $userOrRedirect = self::_checkLogin();
        if ($userOrRedirect instanceof RedirectResponse)
          return $userOrRedirect;
        $guard = self::guard($req, PMC::MNG_AI_TPL, self::REDIRECT_INDEX);
        if ($guard !== true)
          return $guard;
        $template = Template::find($id);
        if (empty($template) || !is_object($template)) {
          Log::warning("{$action} Template not found", [
            'file' => $file,
            'class' => __CLASS__,
            'template_id' => $id
          ]);
          return response()->json([
            'success' => false,
            'message' => __('Template not found.')
          ], 404);
        }
        $fieldJsonStr = $template->fieldJson ?? null;
        $fieldJson = $this->decodeFieldJson($action, $fieldJsonStr);
        if (empty($fieldJson))
          return response()->json([
            'success' => false,
            'message' => __('Invalid template field schema.')
          ], 422);
        $html = '';
        foreach ($fieldJson->field as $field) {
          if (!is_object($field))
            continue;
          $label = e((string)($field->label ?? ''));
          $name = e((string)($field->fieldName ?? ''));
          $ph = e((string)($field->placeholder ?? ''));
          $fieldType = (string)($field->fieldType ?? '');
          if ($fieldType === 'textBox') {
            $html .= '<div class="form-group col-md-12">'
              . '<label class="form-label">' . $label . '</label>'
              . '<input type="text" class="form-control" name="' . $name
              . '" placeholder="' . $ph . '" required></div>';
          } else {
            $html .= '<div class="form-group col-md-12">'
              . '<label class="form-label">' . $label . '</label>'
              . '<textarea rows="3" class="form-control" name="' . $name
              . '" placeholder="' . $ph . '" required></textarea></div>';
          }
        }
        return response()->json([
          'success' => true,
          'tone' => (bool)($template->isTone ?? false),
          self::SINGULAR => $html,
        ]);
      } catch (\Throwable $e) {
        Log::error("{$action} Unexpected error", [
          'file' => $file,
          'class' => __CLASS__,
          'error_class' => get_class($e),
          'message' => $e->getMessage()
        ]);
        return response()->json([
          'success' => false,
          'message' => __('An unexpected error occurred.')
        ], 500);
      }
    });
  }

  public const AIG = 'aiGenerate';
  public function aiGenerate(Request $req): JsonResponse|RedirectResponse
  {
    $action = __CLASS__ . '::' . __FUNCTION__;
    $file = __FILE__;
    return $this->measureProfile($action, function () use ($req, $action, $file) {
      try {
        $userOrRedirect = self::_checkLogin();
        if ($userOrRedirect instanceof RedirectResponse)
          return $userOrRedirect;
        $guard = self::guard($req, PMC::MNG_AI_TPL, self::REDIRECT_INDEX);
        if ($guard !== true)
          return $guard;
        if (!$req->ajax())
          return response()->json(['error' => 'Invalid request'], 400);
        $keyRecord = $this->fetchApiKey($action);
        if (empty($keyRecord))
          return response()->json([
            'status' => 'error',
            'message' => __('Please set proper configuration for Api Key'),
          ], 500);
        $templateName = $req->input('templateName');
        if (empty($templateName))
          return response()->json(['error' => __('Template name is required')], 422);
        $template = Template::find($templateName);
        if (empty($template) || !is_object($template)) {
          Log::warning("{$action} Template not found", [
            'file' => $file,
            'class' => __CLASS__,
            'templateName' => $templateName
          ]);
          return response()->json(['error' => __('Template not found')], 404);
        }
        $fieldJsonStr = $template->fieldJson ?? null;
        $fieldSchema = $this->decodeFieldJson($action, $fieldJsonStr);
        if (empty($fieldSchema))
          return response()->json(['error' => __('Invalid template field schema')], 422);
        foreach ($fieldSchema->field as $field) {
          if (!is_object($field))
            continue;
          $fieldName = (string)($field->fieldName ?? '');
          if (empty($fieldName))
            continue;
          Validator::make(
            [$fieldName => $req->input($fieldName)],
            [$fieldName => 'required|string']
          )->validate();
        }
        $prompt = (string)($template->prompt ?? '');
        foreach ($fieldSchema->field as $field) {
          if (!is_object($field))
            continue;
          $fieldName = (string)($field->fieldName ?? '');
          if (empty($fieldName))
            continue;
          $tag = '##' . $fieldName . '##';
          $value = (string)$req->input($fieldName, '');
          if (str_contains($prompt, $tag))
            $prompt = str_replace($tag, $value, $prompt);
        }
        if (!empty($template->isTone)) {
          $toneVal = (string)$req->input('tone', '');
          $prompt = str_replace('##toneLanguage##', $toneVal, $prompt);
        }
        $language = $req->input('language');
        $langText = 'Provide response in '
          . (is_string($language) && !empty($language) ? $language : 'English')
          . ' language.';
        $temperature = (float)$req->input('aiCreativity', 0.7);
        $maxTokens = max(1, (int)$req->input('resultLength', 256));
        $n = max(1, min(5, (int)$req->input('numOfResult', 1)));
        $response = $this->executeCompletion(
          $action,
          (string)$keyRecord->value,
          $prompt . ' ' . $langText,
          $temperature,
          $maxTokens,
          $n
        );
        if (empty($response) || !isset($response['choices']) || !is_array($response['choices']))
          return response()->json(['error' => __('Invalid OpenAI response')], 500);
        $text = '';
        $i = 1;
        foreach ($response['choices'] as $choice) {
          if (!is_array($choice))
            continue;
          $chunk = ltrim((string)($choice['text'] ?? ''));
          if ($chunk !== '') {
            $text .= ($n > 1 ? ($i++) . '. ' : '') . $chunk . "\n\n";
          }
        }
        return response()->json(['text' => trim($text)]);
      } catch (ValidationException $ve) {
        $errors = $ve->errors();
        $msg = is_array($errors)
          ? (collect($errors)->flatten()->first() ?? __('Validation failed'))
          : __('Validation failed');
        Log::warning("{$action} Validation failed", [
          'file' => $file,
          'class' => __CLASS__,
          'error_class' => get_class($ve),
          'errors' => $errors
        ]);
        return response()->json(['error' => $msg], 422);
      } catch (\InvalidArgumentException $iae) {
        Log::error("{$action} InvalidArgumentException", [
          'file' => $file,
          'class' => __CLASS__,
          'error_class' => get_class($iae),
          'message' => $iae->getMessage()
        ]);
        return response()->json(['error' => __('Invalid input provided')], 422);
      } catch (\RuntimeException $re) {
        Log::error("{$action} RuntimeException", [
          'file' => $file,
          'class' => __CLASS__,
          'error_class' => get_class($re),
          'message' => $re->getMessage()
        ]);
        return response()->json(['error' => __('AI generation failed')], 500);
      } catch (\Throwable $e) {
        Log::error("{$action} failed to generate AI text", [
          'file' => $file,
          'class' => __CLASS__,
          'error_class' => get_class($e),
          'message' => $e->getMessage()
        ]);
        return response()->json(['error' => __('AI generation failed')], 500);
      }
    });
  }

  public function grammar(string $moduleName): View|RedirectResponse
  {
    $action = __CLASS__ . '::' . __FUNCTION__;
    $file = __FILE__;
    $view = self::SINGULAR . '.grammarAi';
    return $this->measureProfile($action, function () use (
      $moduleName,
      $view,
      $action,
      $file
    ) {
      try {
        $userOrRedirect = self::_checkLogin();
        if ($userOrRedirect instanceof RedirectResponse)
          return $userOrRedirect;
        $guard = self::guard(request(), PMC::MNG_AI_TPL, self::REDIRECT_INDEX);
        if ($guard !== true)
          return $guard;
        $template = Template::where('module', $moduleName)->first();
        if (!ViewFacade::exists($view)) {
          Log::error("{$action} View not found", [
            'file' => $file,
            'class' => __CLASS__,
            'view' => $view
          ]);
          return defaultUndefinedException(
            request(),
            new \RuntimeException("View '{$view}' not found"),
            $action
          );
        }
        return view($view, compact('template'));
      } catch (\RuntimeException $re) {
        Log::error("{$action} RuntimeException", [
          'file' => $file,
          'class' => __CLASS__,
          'error_class' => get_class($re),
          'message' => $re->getMessage()
        ]);
        return defaultUndefinedException(request(), $re, $action);
      } catch (\Throwable $e) {
        Log::error("{$action} Unexpected error", [
          'file' => $file,
          'class' => __CLASS__,
          'error_class' => get_class($e),
          'message' => $e->getMessage()
        ]);
        return defaultUndefinedException(request(), $e, $action);
      }
    });
  }

  public const GM_P = 'grammarProcess';
    public const CRT = 'create';

  public function grammarProcess(Request $req): JsonResponse|RedirectResponse
  {
    $action = __CLASS__ . '::' . __FUNCTION__;
    $file = __FILE__;
    return $this->measureProfile($action, function () use ($req, $action, $file) {
      try {
        $userOrRedirect = self::_checkLogin();
        if ($userOrRedirect instanceof RedirectResponse)
          return $userOrRedirect;
        $guard = self::guard($req, PMC::MNG_AI_TPL, self::REDIRECT_INDEX);
        if ($guard !== true)
          return $guard;
        if (!$req->ajax())
          return response()->json(['error' => 'Invalid request'], 400);
        $keyRecord = $this->fetchApiKey($action);
        if (empty($keyRecord))
          return response()->json([
            'status' => 'error',
            'message' => __('Please set proper configuration for Api Key'),
          ], 500);
        $desc = $req->input('description');
        $desc = is_string($desc) ? trim($desc) : '';
        if (empty($desc))
          return response()->json(['error' => __('Description is required')], 422);
        $prompt = "Please correct grammar and spelling mistakes in this text, "
          . "preserving meaning and tone:\n\n" . $desc;
        $maxTokens = max(64, min(2048, (int)ceil(strlen($desc) / 3.5)));
        $response = $this->executeCompletion(
          $action,
          (string)$keyRecord->value,
          $prompt,
          0.2,
          $maxTokens,
          1
        );
        if (
          empty($response)
          || !isset($response['choices'])
          || !is_array($response['choices'])
          || !isset($response['choices'][0])
          || !is_array($response['choices'][0])
          || !isset($response['choices'][0]['text'])
        ) {
          Log::warning("{$action} Invalid OpenAI response structure", [
            'file' => $file,
            'class' => __CLASS__
          ]);
          return response()->json(['error' => __('Invalid OpenAI response')], 500);
        }
        return response()->json([
          'text' => ltrim((string)$response['choices'][0]['text']),
        ]);
      } catch (\InvalidArgumentException $iae) {
        Log::error("{$action} InvalidArgumentException", [
          'file' => $file,
          'class' => __CLASS__,
          'error_class' => get_class($iae),
          'message' => $iae->getMessage()
        ]);
        return response()->json(['error' => __('Invalid input provided')], 422);
      } catch (\RuntimeException $re) {
        Log::error("{$action} RuntimeException", [
          'file' => $file,
          'class' => __CLASS__,
          'error_class' => get_class($re),
          'message' => $re->getMessage()
        ]);
        return response()->json(['error' => __('Grammar check failed')], 500);
      } catch (\Throwable $e) {
        Log::error("{$action} grammar check failed", [
          'file' => $file,
          'class' => __CLASS__,
          'error_class' => get_class($e),
          'message' => $e->getMessage()
        ]);
        return response()->json(['error' => __('Grammar check failed')], 500);
      }
    });
  }
}
