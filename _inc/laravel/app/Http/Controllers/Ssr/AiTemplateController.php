<?php

namespace App\Http\Controllers;

use App\Config\Constants\{DatabaseConstants, PermissionsConstants};
use App\Models\Template;
use App\Traits\{ChecksLogin, ChecksPermissions};
use Illuminate\Http\{
  JsonResponse,
  RedirectResponse,
  Request
};
use Illuminate\Support\Facades\{
  DB,
  Log,
  Validator
};
use Illuminate\View\View;
use Orhanerday\OpenAi\OpenAi;

final class AiTemplateController extends Controller
{
  use ChecksLogin, ChecksPermissions;
  private const SINGULAR = 'template';
  private const REDIRECT_INDEX = '/';

  public function create(string $moduleName): View|RedirectResponse
  {
    if (
      ($userOrRedirect = self::_checkLogin())
      instanceof RedirectResponse
    ) return $userOrRedirect;
    $user = $userOrRedirect;
    if ($c = self::guard(request(), PermissionsConstants::MNG_AI_TPL, self::REDIRECT_INDEX)) return $c;
    $templates = Template::where('module', $moduleName)->get();
    return view(self::SINGULAR . '.generateAi', compact(DatabaseConstants::TABLE_TEMPLATES));
  }

  public function getKeywords(Request $req, int $id): JsonResponse
  {
    if (
      ($userOrRedirect = self::_checkLogin())
      instanceof RedirectResponse
    ) return $userOrRedirect;
    if ($c = self::guard($req, PermissionsConstants::MNG_AI_TPL, self::REDIRECT_INDEX)) return $c;
    $template = Template::find($id);
    $fieldData = json_decode($template->fieldJson);
    $html = '';
    foreach ($fieldData->field as $field)
      $html .= '<div class="form-group col-md-12">
        <label class="form-label ">' . $field->label . '</label>'
        . ($field->fieldType === 'textBox'
          ? '<input type="text" class="form-control" name="' . $field->fieldName
          . '" placeholder="' . $field->placeholder . '" required>;'
          : '<textarea rows=3 class="form-control" name="' . $field->fieldName
          . '" placeholder="' . $field->placeholder . '" required></textarea>;')
        . '</div>';
    return response()->json([
      'success' => true,
      'tone' => $template->isTone,
      self::SINGULAR . '' => $html,
    ]);
  }

  public function aiGenerate(Request $req): JsonResponse
  {
    if (
      ($userOrRedirect = self::_checkLogin())
      instanceof RedirectResponse
    ) return $userOrRedirect;
    if (!$req->ajax()) return response()->json(['error' => 'Invalid request'], 400);
    $post = $req->except([
      '_token', self::SINGULAR . 'Name', 'tone', 'aiCreativity', 'numOfResult', 'resultLength'
    ]);
    $keyRecord = DB::table(DatabaseConstants::TABLE_SETTINGS)
      ->where('name', 'chatGptKey')
      ->first();
    if (!$keyRecord)
      return response()->json([
        'status' => 'error',
        'message' => __('Please set proper configuration for Api Key'),
      ], 500);
    $openAi = new OpenAi($keyRecord->value);
    try {
      $template = Template::find($req->templateName);
      foreach (json_decode($template->fieldJson)->field as $field)
        Validator::make(
          [$field->fieldName => $req->input($field->fieldName)],
          [$field->fieldName => 'required|string']
        )->validate();
      $prompt = $template->prompt;
      foreach (json_decode($template->fieldJson)->field as $field) {
        $tag = '##' . $field->fieldName . '##';
        $prompt = str_contains($prompt, $tag)
          ? str_replace($tag, $post[$field->fieldName] ?? '', $prompt)
          : $prompt;
      }
      if ($template->isTone)
        $prompt = str_replace(
          '##toneLanguage##',
          $req->tone,
          $prompt
        );
      $langText = 'Provide response in ' . $req->language . ' language.';
      $response = json_decode(
        $openAi->completion([
          'model' => 'text-davinci-003',
          'prompt' => $prompt . ' ' . $langText,
          'temperature' => (float)$req->aiCreativity,
          'max_tokens' => (int)$req->resultLength,
          'n' => (int)$req->numOfResult,
        ]),
        true
      );
      if (!isset($response['choices']))
        throw new \RuntimeException('Invalid OpenAI response');
      $text = '';
      $counter = 1;
      foreach ($response['choices'] as $choice)
        $text .= $counter++ . '. ' . ltrim($choice['text']) . "\n\n";
      return response()->json(['text' => trim($text)]);
    } catch (\Throwable $e) {
      Log::error(__CLASS__ . '::' . __FUNCTION__ . " failed to generate AI text: " . $e->getMessage());
      return defaultUndefinedException(
        $req,
        $e,
        __CLASS__ . '::' . __FUNCTION__,
        '/',
        false,
        ['error' => 'AI generation failed']
      );
    }
  }

  public function grammar(string $moduleName): View|RedirectResponse
  {
    if (
      ($userOrRedirect = self::_checkLogin())
      instanceof RedirectResponse
    ) return $userOrRedirect;
    $template = Template::where('module', $moduleName)->first();
    return view(self::SINGULAR . '.grammarAi', compact(self::SINGULAR . ''));
  }

  public function grammarProcess(Request $req): JsonResponse
  {
    if (
      ($userOrRedirect = self::_checkLogin())
      instanceof RedirectResponse
    ) return $userOrRedirect;
    if (!$req->ajax()) return response()->json(['error' => 'Invalid request'], 400);
    $keyRecord = DB::table(DatabaseConstants::TABLE_SETTINGS)
      ->where('name', 'chatGptKey')
      ->first();
    if (!$keyRecord)
      return response()->json([
        'status' => 'error',
        'message' => __('Please set proper configuration for Api Key'),
      ], 500);
    $openAi = new OpenAi($keyRecord->value);
    try {
      $desc = $req->description;
      $prompt = "please correct grammar mistakes and spelling mistakes in this: $desc";
      $response = json_decode(
        $openAi->completion([
          'model' => 'text-davinci-003',
          'prompt' => $prompt,
          'temperature' => 1.0,
          'max_tokens' => strlen($desc),
          'n' => 1,
        ]),
        true
      );
      if (!isset($response['choices']))
        throw new \RuntimeException('Invalid OpenAI response');
      return response()->json([
        'text' => ltrim($response['choices'][0]['text'])
      ]);
    } catch (\Throwable $e) {
      Log::error(__CLASS__ . '::' . __FUNCTION__ . " grammar check failed: " . $e->getMessage());
      return defaultUndefinedException(
        $req,
        $e,
        __CLASS__ . '::' . __FUNCTION__,
        '/',
        false,
        ['error' => 'Grammar check failed']
      );
    }
  }
}
