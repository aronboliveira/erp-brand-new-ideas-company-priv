<?php

namespace App\Http\Controllers;

use App\Config\Constants\{PermissionsConstants, UsersConstants};
use App\Models\{ChFavorite as Favorite, ChMessage as Message, User, Utility};
use App\Traits\ChecksLogin;
use Chatify\Facades\ChatifyMessenger as Chatify;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request, Response};
use Illuminate\{Routing\Controller, Support\Str};
use Illuminate\Support\Facades\{Auth, Log, Response as ResponseFacade, Request as RequestFacade};
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use function App\Http\Controllers\{defaultPermissionDenial, defaultUndefinedException};

class MessagesController extends Controller
{
    use ChecksLogin;

    private const DOWNLOAD_PATH = 'chatify.attachments.folder';

    public const PSH_AUTH = 'pusherAuth';
    public function pusherAuth(Request $request): Response|JsonResponse|null
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $action) {
            try {
                $t = microtime(true);
                self::_checkLogin() instanceof Response && abort(401);
                $this->logExecutionTime($t, $action . '::_checkLogin', 'ok');

                $t = microtime(true);
                $authData = json_encode([
                    UsersConstants::COL_USER_ID => Auth::id(),
                    'user_info' => [UsersConstants::COL_NM => Auth::user()[UsersConstants::COL_NM] ?? '']
                ]);
                $resp = Auth::check()
                    ? Chatify::pusherAuth($request->channel_name, $request->socket_id, $authData)
                    : response('Unauthorized', 401);
                $this->logExecutionTime($t, $action . '::pusherAuth', 'completed');

                return $resp;
            } catch (\Throwable $e) {
                return $this->handleException($request, $e);
            }
        }, ['uri' => $request->getRequestUri(), 'ip' => $request->ip()]);
    }

    public function index(?int $id = null): Response|RedirectResponse
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($id, $action) {
            try {
                $t = microtime(true);
                self::_checkLogin() instanceof Response && abort(401);
                $this->logExecutionTime($t, $action . '::_checkLogin', 'ok');

                $t = microtime(true);
                if (
                    Auth::user()[UsersConstants::COL_TP] === PermissionsConstants::ADM ||
                    Auth::user()[UsersConstants::COL_TP] === PermissionsConstants::SA
                ) {
                    $this->logExecutionTime($t, $action . '::authorize', 'denied');
                    return defaultPermissionDenial(request(), new \Exception, $action);
                }
                $this->logExecutionTime($t, $action . '::authorize', 'ok');

                $t = microtime(true);
                $routeName = RequestFacade::route()->getName();
                $route     = in_array($routeName, ['user', config('chatify.routes.prefix')], true) ? 'user' : $routeName;
                $viewData  = [
                    'id'             => $id ? "{$route}_{$id}" : '0',
                    'route'          => $route,
                    'messengerColor' => Auth::user()->messenger_color,
                    'dark_mode'      => Auth::user()->dark_mode < 1 ? 'light' : 'dark',
                ];
                $resp = response()->view('Chatify::pages.app', $viewData);
                $this->logExecutionTime($t, $action . '::renderView', 'completed');

                return $resp;
            } catch (\Throwable $e) {
                return $this->handleException(request(), $e);
            }
        }, ['id' => $id]);
    }

    public const FETCH_ID_D = 'fetchIdData';
    public function idFetchData(Request $request): JsonResponse
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $action) {
            try {
                $t = microtime(true);
                self::_checkLogin() instanceof Response && abort(401);
                $this->logExecutionTime($t, $action . '::_checkLogin', 'ok');

                $t = microtime(true);
                $favorite = Chatify::inFavorite($request->id);
                $user     = User::findOrFail($request->id);
                $avatar   = $user?->avatar
                    ? Utility::getFile('uploads/avatar/' . $user?->avatar)
                    : Utility::getFile('/' . config('chatify.user_avatar.folder') . '/avatar.png');
                $this->logExecutionTime($t, $action . '::fetchUser', 'completed');

                return JsonResponse::json(compact('favorite', 'user', 'avatar'));
            } catch (\Throwable $e) {
                return $this->handleException($request, $e);
            }
        }, ['id' => $request->id]);
    }

    public function download(string $fileName): JsonResponse|RedirectResponse|BinaryFileResponse
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($fileName, $action) {
            try {
                $t = microtime(true);
                self::_checkLogin() instanceof Response && abort(401);
                $this->logExecutionTime($t, $action . '::_checkLogin', 'ok');

                $t = microtime(true);
                $folder = config(self::DOWNLOAD_PATH);
                $path   = storage_path("$folder/$fileName");
                $resp   = file_exists($path)
                    ? response()->download($path, $fileName)
                    : abort(404, 'File does not exist');
                $this->logExecutionTime($t, $action . '::download', 'completed');

                return $resp;
            } catch (\Throwable $e) {
                return $this->handleException(request(), $e);
            }
        }, ['file' => $fileName]);
    }

    public function send(Request $request): JsonResponse
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $action) {
            try {
                $t = microtime(true);
                self::_checkLogin() instanceof Response && abort(401);
                $this->logExecutionTime($t, $action . '::_checkLogin', 'ok');

                $t = microtime(true);
                $this->authorizeUser($request, 'send message');
                $this->logExecutionTime($t, $action . '::authorizeUser', 'ok');

                $error = ['status' => 0, 'message' => null];
                $attachment = null;
                $attachmentTitle = null;

                if ($file = $request->file('file')) {
                    $t = microtime(true);
                    $allowed = array_merge(Chatify::getAllowedImages(), Chatify::getAllowedFiles());
                    if ($file->getSize() < 150_000_000 && in_array($file->extension(), $allowed, true)) {
                        $attachmentTitle = $file->getClientOriginalName();
                        $attachment      = Str::uuid() . '.' . $file->extension();
                        $upload          = Utility::uploadFile($request, 'file', $attachment, '/attachments/', []);
                        if (($upload['flag'] ?? 0) !== 1) {
                            throw new \RuntimeException($upload['msg'] ?? 'Upload failed');
                        }
                    } else {
                        $error = ['status' => 1, 'message' => 'File invalid or too large'];
                    }
                    $this->logExecutionTime($t, $action . '::handleAttachment', $error['status'] ? 'skipped' : 'completed');
                }

                if (!$error['status']) {
                    $t = microtime(true);
                    $messageId = mt_rand(9, 999_999_999) + time();
                    Chatify::newMessage([
                        'id'         => $messageId,
                        'type'       => $request->type,
                        'from_id'    => Auth::id(),
                        'to_id'      => $request->id,
                        'body'       => htmlentities(trim((string) $request->message), ENT_QUOTES, 'UTF-8'),
                        'attachment' => $attachment
                            ? json_encode((object)[
                                'new_name' => $attachment,
                                'old_name' => htmlentities((string) $attachmentTitle, ENT_QUOTES, 'UTF-8')
                            ])
                            : null,
                    ]);
                    $this->logExecutionTime($t, $action . '::persistMessage', 'completed');

                    $t = microtime(true);
                    $messageData = Chatify::fetchMessage($messageId);
                    Chatify::push('private-chatify', 'messaging', [
                        'from_id' => Auth::id(),
                        'to_id'   => $request->id,
                        'message' => Chatify::messageCard($messageData, 'default'),
                    ]);
                    $this->logExecutionTime($t, $action . '::push', 'completed');
                }

                return JsonResponse::json([
                    'status'      => '200',
                    'error'       => $error['status'],
                    'error_msg'   => $error['message'],
                    'message'     => Chatify::messageCard($messageData ?? null),
                    'tempID'      => $request->temporaryMsgId,
                ]);
            } catch (\Throwable $e) {
                return $this->handleException($request, $e);
            }
        }, ['uri' => $request->getRequestUri()]);
    }

    public function fetch(Request $request): JsonResponse
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $action) {
            try {
                $t = microtime(true);
                self::_checkLogin() instanceof Response && abort(401);
                $this->logExecutionTime($t, $action . '::_checkLogin', 'ok');

                $t = microtime(true);
                $query    = Chatify::fetchMessagesQuery($request->id)->orderBy('created_at', 'asc');
                $messages = $query->get();
                $this->logExecutionTime($t, $action . '::fetchMessages', 'completed');

                $t = microtime(true);
                $html = $messages->reduce(
                    fn($carry, $msg) => $carry . Chatify::messageCard(Chatify::fetchMessage($msg->id)),
                    ''
                );
                $count = $query->count();
                $this->logExecutionTime($t, $action . '::renderMessages', 'completed');

                return JsonResponse::json([
                    'count'    => $count,
                    'messages' => $count ? $html : '<p class="message-hint"><span>Say \'hi\' and start messaging</span></p>',
                ]);
            } catch (\Throwable $e) {
                return $this->handleException($request, $e);
            }
        }, ['id' => $request->id]);
    }

    public function seen(Request $request): JsonResponse
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $action) {
            try {
                $t = microtime(true);
                self::_checkLogin() instanceof Response && abort(401);
                $this->logExecutionTime($t, $action . '::_checkLogin', 'ok');

                $t = microtime(true);
                $unseen      = Message::where('from_id', $request->id)->where('to_id', Auth::id())->where('seen', 0)->count();
                $totalUnseen = Message::where('to_id', Auth::id())->where('seen', 0)->count();
                $seenCount   = Chatify::makeSeen($request->id);
                $newCount    = $seenCount ? $totalUnseen - $unseen : $totalUnseen;
                $this->logExecutionTime($t, $action . '::markSeen', 'completed');

                return JsonResponse::json(['status' => $seenCount, 'messengerCount' => $newCount], 200);
            } catch (\Throwable $e) {
                return $this->handleException($request, $e);
            }
        }, ['peer_id' => $request->id]);
    }

    public const GET_CTT = 'getContacts';
    public function getContacts(Request $request): JsonResponse|RedirectResponse
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $action) {
            try {
                $t = microtime(true);
                if (($userOrRedirect = self::_checkLogin()) instanceof \Illuminate\Http\RedirectResponse)
                    return $userOrRedirect;
                $user = $userOrRedirect;
                $this->logExecutionTime($t, $action . '::_checkLogin', 'ok');

                $t = microtime(true);
                $userId = Auth::id();
                $users  = Message::join('users', function ($j) {
                    $j->on('ch_messages.from_id', '=', 'users.id')
                        ->orOn('ch_messages.to_id', '=', 'users.id');
                })
                    ->where(function ($q) use ($userId) {
                        $q->where('ch_messages.from_id', $userId)
                            ->orWhere('ch_messages.to_id', $userId);
                    })
                    ->orderBy('ch_messages.created_at', 'desc')
                    ->select('users.*')
                    ->get()
                    ->unique('id');
                $this->logExecutionTime($t, $action . '::fetchContacts', 'completed');

                $t = microtime(true);
                $contacts = $users->reject(fn($u) => $u->id === $userId)
                    ->reduce(fn($html, $u) => $html . Chatify::getContactItem($request->messenger_id, $u), '');
                $this->logExecutionTime($t, $action . '::renderContacts', 'completed');

                $t = microtime(true);
                $members = User::where('type', '!=', 'client')
                    ->where('created_by', $user?->creatorId())
                    ->when(
                        $user[UsersConstants::COL_TP] !== 'company',
                        fn($q) => $q->where('id', '!=', $userId)->orWhere('id', $user?->creatorId())
                    )
                    ->get();
                $allUsers = $members->reduce(
                    fn($h, $m) => $h . view(
                        'vendor.Chatify.layouts.list_item',
                        ['get' => 'all_members', 'type' => 'user', 'user' => $m]
                    )->render(),
                    ''
                );
                $this->logExecutionTime($t, $action . '::renderMembers', 'completed');

                return JsonResponse::json([
                    'contacts' => $contacts ?: '<p class="message-hint"><span>' . __('Your contact list is empty') . '</span></p>',
                    'allUsers' => $allUsers ?: '<p class="message-hint"><span>' . __('Your member list is empty') . '</span></p>',
                ], 200);
            } catch (\Throwable $e) {
                return $this->handleException($request, $e);
            }
        }, ['uri' => $request->getRequestUri()]);
    }

    /**
     * Update user's list item data
     *
     * @return JSON response
     */
    public const UPD_CTT_IT = 'updateContactItem';
    public function updateContactItem(Request $request)
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $action) {
            $t = microtime(true);
            $userCollection = User::where('id', $request[UsersConstants::COL_USER_ID])->first();
            $contactItem    = Chatify::getContactItem($request['messenger_id'], $userCollection);
            $messageCount   = Message::where('to_id', Auth::user()->id)->where('seen', 0)->count();
            $this->logExecutionTime($t, $action . '::compose', 'completed');

            return Response::json(
                ['contactItem' => $contactItem, 'messengerCount' => $messageCount],
                200
            );
        }, ['user_id' => $request[UsersConstants::COL_USER_ID] ?? null]);
    }

    /** Toggle favorite status for a user. */
    public function favorite(Request $request): JsonResponse
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $action) {
            try {
                $t = microtime(true);
                self::_checkLogin() instanceof Response && abort(401);
                $this->logExecutionTime($t, $action . '::_checkLogin', 'ok');

                $t = microtime(true);
                $userId = $request->user_id;
                $isFav  = Chatify::inFavorite($userId);
                Chatify::makeInFavorite($userId, $isFav ? 0 : 1);
                $this->logExecutionTime($t, $action . '::toggleFavorite', 'completed');

                return JsonResponse::json(['status' => $isFav ? 0 : 1], 200);
            } catch (\Throwable $e) {
                Log::error($action . ' failed', ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action);
            }
        }, ['target_user' => $request->user_id ?? null]);
    }

    /** Retrieve list of favorite contacts. */
    public const GET_FAV = 'getFavorites';
    public function getFavorites(Request $request): JsonResponse
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $action) {
            try {
                $t = microtime(true);
                self::_checkLogin() instanceof Response && abort(401);
                $this->logExecutionTime($t, $action . '::_checkLogin', 'ok');

                $t = microtime(true);
                $favorites = Favorite::where(UsersConstants::COL_USER_ID, Auth::id())->get();
                $count     = $favorites->count();
                $html      = $favorites->reduce(
                    fn($h, $fav) => $h
                        . view('Chatify::layouts.favorite', ['user' => User::find($fav->favorite_id)])->render(),
                    ''
                );
                $this->logExecutionTime($t, $action . '::fetchFavorites', 'completed');

                return JsonResponse::json([
                    'count'     => $count,
                    'favorites' => $count
                        ? $html
                        : '<p class="message-hint"><span>' . __("Your favorite list is empty") . '</span></p>',
                ], 200);
            } catch (\Throwable $e) {
                Log::error($action . ' failed', ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action);
            }
        });
    }

    /** Search users by name. */
    public function search(Request $request): JsonResponse|RedirectResponse
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $action) {
            try {
                $t = microtime(true);
                if (($userOrRedirect = self::_checkLogin()) instanceof \Illuminate\Http\RedirectResponse)
                    return $userOrRedirect;
                $user = $userOrRedirect;
                $this->logExecutionTime($t, $action . '::_checkLogin', 'ok');

                $t = microtime(true);
                $rawInput = $request->input('input', '');
                $term     = htmlspecialchars(strip_tags(trim($rawInput)), ENT_QUOTES, 'UTF-8');
                $records  = User::where('created_by', $user?->creatorId())
                    ->where('type', '!=', 'client')
                    ->where(UsersConstants::COL_NM, 'LIKE', "%{$term}%")
                    ->get();
                $this->logExecutionTime($t, $action . '::query', 'completed');

                $t = microtime(true);
                $html = $records->reduce(
                    fn($h, $r) => $h . view('Chatify::layouts.listItem', [
                        'get'  => 'search_item',
                        'type' => 'user',
                        'user' => $r,
                    ])->render(),
                    ''
                );
                $this->logExecutionTime($t, $action . '::render', 'completed');

                return JsonResponse::json([
                    'records' => $records->count()
                        ? $html
                        : '<p class="message-hint"><span>Nothing to show.</span></p>',
                    'addData' => 'html',
                ], 200);
            } catch (\Throwable $e) {
                Log::error($action . ' failed', ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action);
            }
        }, ['q' => $request->input('input', '')]);
    }

    /** Get shared photos between users. */
    public const SHD_PHS = 'sharedPhotos';
    public function sharedPhotos(Request $request): JsonResponse
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $action) {
            try {
                $t = microtime(true);
                self::_checkLogin() instanceof Response && abort(401);
                $this->logExecutionTime($t, $action . '::_checkLogin', 'ok');

                $t = microtime(true);
                $shared = Chatify::getSharedPhotos($request->user_id);
                $html   = collect($shared)->reduce(
                    fn($h, $img) => $h
                        . view('Chatify::layouts.listItem', [
                            'get'   => 'sharedPhoto',
                            'image' => Utility::getFile("attachments/{$img}")
                        ])->render(),
                    ''
                );
                $this->logExecutionTime($t, $action . '::renderShared', 'completed');

                return JsonResponse::json([
                    'shared' => count($shared)
                        ? $html
                        : '<p class="message-hint"><span>Nothing shared yet</span></p>',
                ], 200);
            } catch (\Throwable $e) {
                Log::error($action . ' failed', ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action);
            }
        }, ['peer_id' => $request->user_id ?? null]);
    }

    /** Delete an entire conversation. */
    public const DEL_CVT = 'deleteConversation';
    public function deleteConversation(Request $request): JsonResponse
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $action) {
            try {
                $t = microtime(true);
                self::_checkLogin() instanceof Response && abort(401);
                $this->logExecutionTime($t, $action . '::_checkLogin', 'ok');

                $t = microtime(true);
                $deleted = Chatify::deleteConversation($request->id) ? 1 : 0;
                $this->logExecutionTime($t, $action . '::delete', 'completed');

                return JsonResponse::json(['deleted' => $deleted], 200);
            } catch (\Throwable $e) {
                Log::error($action . ' failed', ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action);
            }
        }, ['peer_id' => $request->id ?? null]);
    }

    /** Update user display settings (dark mode, color, avatar). */
    public const UPD_STG = 'updateSettings';
    public function updateSettings(Request $request): JsonResponse|RedirectResponse
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $action) {
            try {
                $t = microtime(true);
                if (($userOrRedirect = self::_checkLogin()) instanceof \Illuminate\Http\RedirectResponse)
                    return $userOrRedirect;
                $user = $userOrRedirect;
                $this->logExecutionTime($t, $action . '::_checkLogin', 'ok');

                $success = 0;
                $error = 0;
                $msg = null;

                $t = microtime(true);
                if ($mode = $request->input('dark_mode')) {
                    $user->update(['dark_mode' => $mode === 'dark' ? 1 : 0]);
                }
                $this->logExecutionTime($t, $action . '::setDarkMode', 'completed');

                $t = microtime(true);
                if ($rawColor = $request->input('messengerColor')) {
                    $colorKey = explode('-', strip_tags(trim($rawColor)))[1] ?? null;
                    $colors   = Chatify::getMessengerColors();
                    if ($colorKey !== null && isset($colors[$colorKey])) {
                        $user->update(['messenger_color' => $colors[$colorKey]]); // fixed => bug
                    }
                }
                $this->logExecutionTime($t, $action . '::setColor', 'completed');

                if ($file = $request->file('avatar')) {
                    $t = microtime(true);
                    $allowed = Chatify::getAllowedImages();
                    $size    = $file->getSize();
                    $ext     = strtolower($file->getClientOriginalExtension());
                    if ($size < 150_000_000 && in_array($ext, $allowed, true)) {
                        if ($user->avatar != config('chatify.user_avatar.default')) {
                            $old = storage_path(config('chatify.user_avatar.folder') . "/{$user?->avatar}");
                            if (file_exists($old)) @unlink($old);
                        }
                        $avatar = (string) Str::uuid() . ".{$ext}";
                        $file->storeAs(config('chatify.user_avatar.folder'), $avatar);
                        $success = $user->update(['avatar' => $avatar]) ? 1 : 0; // fixed => bug
                    } else {
                        $error = 1;
                        $msg = 'File extension not allowed or too large!';
                    }
                    $this->logExecutionTime($t, $action . '::uploadAvatar', $error ? 'failed' : 'completed');
                }

                return JsonResponse::json([
                    'status'  => $success,
                    'error'   => $error,
                    'message' => $error ? $msg : 0,
                ], 200);
            } catch (\Throwable $e) {
                Log::error($action . ' failed', ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action);
            }
        }, ['uri' => $request->getRequestUri()]);
    }

    /** Set another user’s active status. */
    public const SET_ACT_STT = 'setActiveStatus';
    public function setActiveStatus(Request $request): JsonResponse
    {
        $action = class_basename(static::class) . '@' . __FUNCTION__;
        return $this->measureProfile($action, function () use ($request, $action) {
            try {
                $t = microtime(true);
                self::_checkLogin() instanceof Response && abort(401);
                $this->logExecutionTime($t, $action . '::_checkLogin', 'ok');

                $t = microtime(true);
                $userId = $request->user_id;
                $status = $request->status > 0 ? 1 : 0;
                $updated = User::where('id', $userId)->update(['active_status' => $status]);
                $this->logExecutionTime($t, $action . '::persist', 'completed');

                return JsonResponse::json(['status' => $updated], 200);
            } catch (\Throwable $e) {
                Log::error($action . ' failed', ['error' => $e->getMessage()]);
                return defaultUndefinedException($request, $e, $action);
            }
        }, ['target_user' => $request->user_id ?? null, 'status' => $request->status ?? null]);
    }


    private function authorizeUser(Request $request, string $permission)
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        if (($user = self::_checkLogin()) instanceof Response) return $user;
        if (!$user?->can($permission))
            throw new \Illuminate\Auth\Access\AuthorizationException;
    }

    private function handleException(Request $request, \Throwable $e): RedirectResponse|JsonResponse|null
    {
        Log::error(__METHOD__ . ' failed', ['error' => $e->getMessage()]);
        return defaultUndefinedException($request, $e, __METHOD__);
    }
}
