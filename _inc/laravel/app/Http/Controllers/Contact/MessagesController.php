<?php

namespace App\Http\Controllers;

use App\Config\Constants\{PermissionsConstants, UsersConstants};
use App\Models\{ChFavorite as Favorite, ChMessage as Message, User, Utility};
use App\Traits\ChecksLogin;
use Chatify\Facades\ChatifyMessenger as Chatify;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request, Response};
use Illuminate\{Routing\Controller, Support\Str};
use Illuminate\Support\Facades\{Auth, Log, Response as ResponseFacade, Request as RequestFacade};
use function App\Http\Controllers\{defaultPermissionDenial, defaultUndefinedException};

class MessagesController extends Controller
{
    use ChecksLogin;

    private const DOWNLOAD_PATH = 'chatify.attachments.folder';

    public function pusherAuth(Request $request): Response
    {
        try {
            self::_checkLogin() instanceof Response && abort(401);
            $authData = json_encode([
                UsersConstants::COL_USER_ID => Auth::id(),
                'user_info' => [UsersConstants::COL_NM => Auth::user()[UsersConstants::COL_NM]]
            ]);
            return Auth::check()
                ? Chatify::pusherAuth($request->channel_name, $request->socket_id, $authData)
                : response('Unauthorized', 401);
        } catch (\Throwable $e) {
            return $this->handleException($request, $e);
        }
    }

    public function index(?int $id = null): Response
    {
        try {
            self::_checkLogin() instanceof Response && abort(401);
            if (
                Auth::user()[UsersConstants::COL_TP] === PermissionsConstants::ADM ||
                Auth::user()[UsersConstants::COL_TP] === PermissionsConstants::SA
            )
                return defaultPermissionDenial(request(), new \Exception, __CLASS__ . '::' . __FUNCTION__);
            $routeName = RequestFacade::route()->getName();
            $route    = in_array($routeName, ['user', config('chatify.routes.prefix')]) ? 'user' : $routeName;
            $viewData = [
                'id'             => $id ? "$route\_$id" : '0',
                'route'          => $route,
                'messengerColor' => Auth::user()->messenger_color,
                'dark_mode'      => Auth::user()->dark_mode < 1 ? 'light' : 'dark',
            ];
            return response()->view('Chatify::pages.app', $viewData);
        } catch (\Throwable $e) {
            return $this->handleException(request(), $e);
        }
    }

    public function idFetchData(Request $request): JsonResponse
    {
        try {
            self::_checkLogin() instanceof Response && abort(401);
            $favorite = Chatify::inFavorite($request->id);
            $user    = User::findOrFail($request->id);
            $avatar  = $user?->avatar
                ? Utility::getFile('uploads/avatar/' . $user?->avatar)
                : Utility::getFile('/' . config('chatify.user_avatar.folder') . '/avatar.png');
            return JsonResponse::json(compact('favorite', 'user', 'avatar'));
        } catch (\Throwable $e) {
            $this->handleException($request, $e);
        }
    }

    public function download(string $fileName)
    {
        try {
            self::_checkLogin() instanceof Response && abort(401);
            $folder = config(self::DOWNLOAD_PATH);
            $path  = storage_path("$folder/$fileName");
            return file_exists($path)
                ? response()->download($path, $fileName)
                : abort(404, "File does not exist");
        } catch (\Throwable $e) {
            return $this->handleException(request(), $e);
        }
    }

    public function send(Request $request): JsonResponse
    {
        try {
            self::_checkLogin() instanceof Response && abort(401);
            $this->authorizeUser($request, 'send message');
            $error           = ['status' => 0, 'message' => null];
            $attachment      = null;
            $attachmentTitle = null;
            if ($file = $request->file('file')) {
                $allowed = array_merge(Chatify::getAllowedImages(), Chatify::getAllowedFiles());
                if ($file->getSize() < 150_000_000 && in_array($file->extension(), $allowed)) {
                    $attachmentTitle = $file->getClientOriginalName();
                    $attachment     = Str::uuid() . '.' . $file->extension();
                    $upload         = Utility::uploadFile($request, 'file', $attachment, '/attachments/', []);
                    if ($upload['flag'] !== 1) throw new \RuntimeException($upload['msg']);
                } else $error = ['status' => 1, 'message' => 'File invalid or too large'];
            }
            if (!$error['status']) {
                $messageId = mt_rand(9, 999_999_999) + time();
                Chatify::newMessage([
                    'id'         => $messageId,
                    'type'       => $request->type,
                    'from_id'    => Auth::id(),
                    'to_id'      => $request->id,
                    'body'       => htmlentities(trim($request->message), ENT_QUOTES, 'UTF-8'),
                    'attachment' => $attachment
                        ? json_encode((object)['new_name' => $attachment, 'old_name' => htmlentities($attachmentTitle, ENT_QUOTES, 'UTF-8')])
                        : null,
                ]);
                $messageData = Chatify::fetchMessage($messageId);
                Chatify::push('private-chatify', 'messaging', [
                    'from_id' => Auth::id(),
                    'to_id'   => $request->id,
                    'message' => Chatify::messageCard($messageData, 'default'),
                ]);
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
    }

    public function fetch(Request $request): JsonResponse
    {
        try {
            self::_checkLogin() instanceof Response && abort(401);
            $query   = Chatify::fetchMessagesQuery($request->id)->orderBy('created_at', 'asc');
            $messages = $query->get();
            $html    = $messages->reduce(fn ($carry, $msg) => $carry . Chatify::messageCard(Chatify::fetchMessage($msg->id)), '');
            return JsonResponse::json([
                'count'    => $query->count(),
                'messages' => $query->count() ? $html : '<p class="message-hint"><span>Say \'hi\' and start messaging</span></p>',
            ]);
        } catch (\Throwable $e) {
            return $this->handleException($request, $e);
        }
    }

    public function seen(Request $request): JsonResponse
    {
        try {
            self::_checkLogin() instanceof Response && abort(401);
            $unseen      = Message::where('from_id', $request->id)->where('to_id', Auth::id())->where('seen', 0)->count();
            $totalUnseen = Message::where('to_id', Auth::id())->where('seen', 0)->count();
            $seenCount   = Chatify::makeSeen($request->id);
            $newCount    = $seenCount ? $totalUnseen - $unseen : $totalUnseen;
            return JsonResponse::json(['status' => $seenCount, 'messengerCount' => $newCount], 200);
        } catch (\Throwable $e) {
            return $this->handleException($request, $e);
        }
    }

    public function getContacts(Request $request): JsonResponse
    {
        try {
            if (
                ($userOrRedirect = self::_checkLogin())
                instanceof \Illuminate\Http\RedirectResponse
            )
                return $userOrRedirect;
            $user = $userOrRedirect;
            self::_checkLogin() instanceof Response && abort(401);
            $userId = Auth::id();
            $users  = Message::join('users', fn ($j) => $j->on('ch_messages.from_id', 'users.id')->orOn('ch_messages.to_id', 'users.id'))
                ->where('ch_messages.from_id', $userId)->orWhere('ch_messages.to_id', $userId)
                ->orderBy('ch_messages.created_at', 'desc')->get()->unique('id');
            $contacts = $users->reject(fn ($u) => $u->id === $userId)
                ->reduce(fn ($html, $u) => $html . Chatify::getContactItem($request->messenger_id, $u), '');
            $members = User::where('type', '!=', 'client')->where('created_by', $user?->creatorId())
                ->when($user[UsersConstants::COL_TP] !== 'company', fn ($q) => $q->where('id', '!=', $userId)->orWhere('id', $user?->creatorId()))
                ->get();
            $allUsers = $members->reduce(fn ($h, $m) => $h . view('vendor.Chatify.layouts.listItem', ['get' => 'all_members', 'type' => 'user', 'user' => $m])->render(), '');
            return JsonResponse::json([
                'contacts' => $contacts ?: '<p class="message-hint"><span>' . __('Your contact list is empty') . '</span></p>',
                'allUsers' => $allUsers ?: '<p class="message-hint"><span>' . __('Your member list is empty') . '</span></p>',
            ], 200);
        } catch (\Throwable $e) {
            return $this->handleException($request, $e);
        }
    }

    /**
     * Update user's list item data
     *
     * @param Request $request
     *
     * @return JSON response
     */
    public function updateContactItem(Request $request)
    {
        // Get user data
        $userCollection = User::where('id', $request[UsersConstants::COL_USER_ID])->first();
        $contactItem   = Chatify::getContactItem($request['messenger_id'], $userCollection);
        $messageCount = Message::where('to_id', Auth::user()->id)->where('seen', 0)->count();
        // send the response
        return Response::json(
            [
                'contactItem' => $contactItem,
                'messengerCount' => $messageCount
            ],
            200
        );
    }
    /**
     * Toggle favorite status for a user.
     */
    public function favorite(Request $request): JsonResponse
    {
        try {
            self::_checkLogin() instanceof Response && abort(401);
            $userId = $request->user_id;
            $isFav = Chatify::inFavorite($userId);
            Chatify::makeInFavorite($userId, $isFav ? 0 : 1);
            return JsonResponse::json(['status' => $isFav ? 0 : 1], 200);
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed', ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    /**
     * Retrieve list of favorite contacts.
     */
    public function getFavorites(Request $request): JsonResponse
    {
        try {
            self::_checkLogin() instanceof Response && abort(401);
            $favorites = Favorite::where(UsersConstants::COL_USER_ID, Auth::id())->get();
            $count    = $favorites->count();
            $html     = $favorites->reduce(
                fn ($h, $fav) => $h
                    . view('Chatify::layouts.favorite', ['user' => User::find($fav->favorite_id)])->render(),
                ''
            );
            return JsonResponse::json([
                'count'     => $count,
                'favorites' => $count
                    ? $html
                    : '<p class="message-hint"><span>' . __("Your favorite list is empty") . '</span></p>',
            ], 200);
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed', ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    /**
     * Search users by name.
     */
    public function search(Request $request): JsonResponse
    {
        try {
            if (
                ($userOrRedirect = self::_checkLogin())
                instanceof \Illuminate\Http\RedirectResponse
            )
                return $userOrRedirect;
            $user = $userOrRedirect;
            $rawInput = $request->input('input', '');
            $term    = htmlspecialchars(strip_tags(trim($rawInput)), ENT_QUOTES, 'UTF-8');
            $records = User::where('created_by', $user?->creatorId())
                ->where('type', '!=', 'client')
                ->where(UsersConstants::COL_NM, 'LIKE', "%{$term}%")
                ->get();
            $html = $records->reduce(
                fn ($h, $r) => $h
                    . view('Chatify::layouts.listItem', [
                        'get'  => 'search_item',
                        'type' => 'user',
                        'user' => $r,
                    ])->render(),
                ''
            );
            return JsonResponse::json([
                'records' => $records->count()
                    ? $html
                    : '<p class="message-hint"><span>Nothing to show.</span></p>',
                'addData' => 'html',
            ], 200);
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed', ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    /**
     * Get shared photos between users.
     */
    public function sharedPhotos(Request $request): JsonResponse
    {
        try {
            self::_checkLogin() instanceof Response && abort(401);
            $shared = Chatify::getSharedPhotos($request->user_id);
            $html  = collect($shared)->reduce(
                fn ($h, $img) => $h
                    . view('Chatify::layouts.listItem', [
                        'get'   => 'sharedPhoto',
                        'image' => Utility::getFile("attachments/{$img}")
                    ])->render(),
                ''
            );
            return JsonResponse::json([
                'shared' => count($shared)
                    ? $html
                    : '<p class="message-hint"><span>Nothing shared yet</span></p>',
            ], 200);
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed', ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    /**
     * Delete an entire conversation.
     */
    public function deleteConversation(Request $request): JsonResponse
    {
        try {
            self::_checkLogin() instanceof Response && abort(401);
            $deleted = Chatify::deleteConversation($request->id) ? 1 : 0;
            return JsonResponse::json(['deleted' => $deleted], 200);
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed', ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    /**
     * Update user display settings (dark mode, color, avatar).
     */
    public function updateSettings(Request $request): JsonResponse
    {
        try {
            if (
                ($userOrRedirect = self::_checkLogin())
                instanceof \Illuminate\Http\RedirectResponse
            )
                return $userOrRedirect;
            $user = $userOrRedirect;
            $success = 0;
            $error  = 0;
            $msg    = null;
            if ($mode = $request->input('dark_mode'))
                $user->update(['dark_mode' => $mode == 'dark' ? 1 : 0]);
            if ($rawColor = $request->input('messengerColor')) {
                $colorKey = explode('-', strip_tags(trim($rawColor)))[1] ?? null;
                $colors  = Chatify::getMessengerColors();
                isset($colors[$colorKey]) && $user->update(['messenger_color' > $colors[$colorKey]]);
            }
            if ($file = $request->file('avatar')) {
                $allowed = Chatify::getAllowedImages();
                $size   = $file->getSize();
                $ext    = $file->getClientOriginalExtension();
                if ($size < 150_000_000 && in_array($ext, $allowed, true)) {
                    if ($user->avatar != config('chatify.user_avatar.default')) {
                        $old = storage_path(config('chatify.user_avatar.folder') . "/{$user?->avatar}");
                        file_exists($old) && @unlink($old);
                    }
                    $avatar  = Str::uuid() . ".$ext";
                    $file->storeAs('avatar', $avatar);
                    $success = $user->update(['avatar' > $avatar]) ? 1 : 0;
                } else {
                    $error = 1;
                    $msg  = 'File extension not allowed or too large!';
                }
            }

            return JsonResponse::json([
                'status'  => $success,
                'error'   => $error,
                'message' => $error ? $msg : 0,
            ], 200);
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed', ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    /**
     * Set another user’s active status.
     */
    public function setActiveStatus(Request $request): JsonResponse
    {
        try {
            self::_checkLogin() instanceof Response && abort(401);
            $userId = $request->user_id;
            $status = $request->status > 0 ? 1 : 0;
            $updated = User::where('id', $userId)->update(['active_status' => $status]);
            return JsonResponse::json(['status' => $updated], 200);
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed', ['error' => $e->getMessage()]);
            return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
        }
    }

    private function authorizeUser(Request $request, string $permission): User|RedirectResponse|null
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
        Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed', ['error' => $e->getMessage()]);
        return defaultUndefinedException($request, $e, __CLASS__ . '::' . __FUNCTION__);
    }
}
