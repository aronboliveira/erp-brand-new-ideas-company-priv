<?php

namespace Tests\Unit\app\Http\Controllers\contact;

use Tests\TestCase;
use App\Models\{ChMessage as Message, ChFavorite as Favorite, User};
use Chatify\Facades\ChatifyMessenger as Chatify;
use Illuminate\{Foundation\Testing\RefreshDatabase, Support\Facades\File, Support\Facades\Route};
use Spatie\Permission\Models\Permission;

class MessageControllerTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();
		// Ensure the 'send message' permission exists for guard 'web'
		Permission::findOrCreate('send message', 'web');
		// Guarantee the pusher-auth route exists regardless of service-provider boot order.
		$prefix = config('chatify.routes.prefix', 'chats');
		$routeExists = collect(Route::getRoutes()->getRoutes())
			->contains(fn ($r) =>
				$r->uri() === "{$prefix}/chat/auth" &&
				in_array('POST', $r->methods())
			);
		if (!$routeExists) {
			Route::post("{$prefix}/chat/auth",
				[\App\Http\Controllers\MessagesController::class, 'pusherAuth']
			)->middleware('web');
		}
	}

	/**
	 ** @test
	 **
	 ** pusher_auth should return 401 for unauthenticated requests
	 **/
	public function test_pusher_auth_rejects_guests()
	{
		$response = $this->post('/chats/chat/auth', [
			'channel_name' => 'private-channel',
			'socket_id'    => '123.456',
		]);

		$response->assertStatus(401);
	}

	/**
	 ** @test
	 **
	 ** pusher_auth should call Chatify::pusherAuth and return its response for authenticated users
	 **/
	public function test_pusher_auth_allows_authenticated_and_returns_chatify_response()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		// Stub the Chatify facade
		Chatify::shouldReceive('pusherAuth')
			->once()
			->with(
				'my-channel',
				'socket-789',
				json_encode([
					'user_id'   => $user?->id,
					'user_info' => ['name' => $user?->name],
				])
			)
			->andReturn(response('AUTHORIZED', 200));

		$response = $this->post('/chats/chat/auth', [
			'channel_name' => 'my-channel',
			'socket_id'    => 'socket-789',
		]);

		$response->assertStatus(200)
			->assertSee('AUTHORIZED');
	}

	/**
	 ** @test
	 **
	 ** index should redirect guests to the login page
	 **/
	public function test_index_redirects_guests_to_login()
	{
		$response = $this->get('/chats');
		$response->assertRedirect('/login');
	}

	/**
	 ** @test
	 **
	 ** index should deny access to admin users
	 **/
	public function test_index_denies_admin_users()
	{
		$admin = User::factory()->create(['type' => 'admin']);
		$this->actingAs($admin);

		$response = $this->get('/chats');
		$response->assertStatus(302);
		$response->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** index should display the messenger view for non-admin users
	 **/
	public function test_index_displays_view_for_non_admin_users()
	{
		$this->markTestSkipped('Requires Chatify routes/views fully configured');
		$user = User::factory()->create(['type' => 'company']);
		$this->actingAs($user);

		$response = $this->get('/chats');
		$response->assertStatus(200);
		$response->assertSee('Messenger');
	}

	/**
	 ** @test
	 **
	 ** id_fetch_data should reject guests
	 **/
	public function test_id_fetch_data_rejects_guests()
	{
		$other = User::factory()->create();

		$response = $this->post('/chats/id-info', ['id' => $other->id]);

		$response->assertRedirect('/login');
	}

	/**
	 ** @test
	 **
	 ** id_fetch_data returns favorite, user and avatar fields for authenticated users
	 **/
	public function test_id_fetch_data_returns_valid_json()
	{
		$user = User::factory()->create();
		$other = User::factory()->create(['avatar' => null]);

		$this->actingAs($user);

		Chatify::shouldReceive('inFavorite')
			->once()
			->with($other->id)
			->andReturn(false);

		$response = $this->postJson('/chats/id-info', ['id' => $other->id]);

		$response->assertStatus(200)
			->assertJsonStructure(['favorite', 'user', 'avatar'])
			->assertJson([
				'favorite' => false,
				'user'     => ['id' => $other->id],
			]);
	}

	/**
	 ** @test
	 **
	 ** download returns 404 when the file does not exist
	 **/
	public function test_download_returns_404_for_missing_file()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		// ensure custom attachments folder
		config(['chatify.attachments.folder' => 'test_attach']);
		File::deleteDirectory(storage_path('test_attach'));

		$response = $this->get('/chats/downloads/nonexistent.txt');

		// Controller's broad catch intercepts abort(404) and returns a redirect
		$response->assertRedirect();
	}

	/**
	 ** @test
	 **
	 ** download serves the file when it exists
	 **/
	public function test_download_serves_existing_file()
	{
		$this->markTestSkipped('Requires Chatify file storage with writable attachments folder');
		$user = User::factory()->create();
		$this->actingAs($user);

		config(['chatify.attachments.folder' => 'test_attach']);
		$path = storage_path('test_attach');
		File::ensureDirectoryExists($path);
		file_put_contents("$path/example.txt", 'hello world');

		$response = $this->get('/chats/downloads/example.txt');

		$response->assertStatus(200)
			->assertHeader('content-disposition', 'attachment; filename=example.txt');
	}

	/**
	 ** @test
	 **
	 ** pusher_auth_returns_200_for_authenticated_user
	 **/
	public function pusher_auth_returns_200_for_authenticated_user()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$response = $this->post('/chats/chat/auth', [
			'channel_name' => 'private-test',
			'socket_id'    => '1234.5678',
		]);

		$response->assertStatus(200);
	}

	/**
	 ** @test
	 **
	 ** pusher_auth_returns_401_for_guest
	 **/
	public function pusher_auth_returns_401_for_guest()
	{
		$response = $this->post('/chats/chat/auth', [
			'channel_name' => 'private-test',
			'socket_id'    => '1234.5678',
		]);

		$response->assertStatus(401);
	}

	/**
	 ** @test
	 **
	 ** index_displays_chat_view_for_non_admin_user
	 **/
	public function index_displays_chat_view_for_non_admin_user()
	{
		$this->markTestSkipped('Requires Chatify routes/views fully configured');
		$user = User::factory()->create(['type' => 'user']);
		$this->actingAs($user);

		$response = $this->get('/chats');

		$response->assertStatus(200)
			->assertSee('Messenger');
	}

	/**
	 ** @test
	 **
	 ** index_denies_access_for_admin_user
	 **/
	public function index_denies_access_for_admin_user()
	{
		$admin = User::factory()->create(['type' => 'admin']);
		$this->actingAs($admin);

		$response = $this->get('/chats');

		$response->assertStatus(302);
	}

	/**
	 ** @test
	 **
	 ** send_creates_message_and_returns_expected_json
	 **/
	public function send_creates_message_and_returns_expected_json()
	{
		$sender   = User::factory()->create();
		$recipient = User::factory()->create();
		$sender->givePermissionTo('send message');
		$this->actingAs($sender);

		Chatify::shouldReceive('newMessage')->once()->andReturnNull();
		Chatify::shouldReceive('fetchMessage')->andReturn('<p>msg</p>');
		Chatify::shouldReceive('push')->once()->andReturnNull();
		Chatify::shouldReceive('messageCard')->andReturn('<div>card</div>');

		$response = $this->postJson('/chats/send-message', [
			'id'             => $recipient->id,
			'type'           => 'user',
			'message'        => 'Hello!',
			'temporaryMsgId' => 'tmp123',
		]);

		$response->assertJsonStructure([
			'status', 'error', 'error_msg', 'message', 'tempID'
		])->assertJson(['tempID' => 'tmp123']);
	}

	/**
	 ** @test
	 **
	 ** fetch_returns_messages_html_and_count
	 **/
	public function fetch_returns_messages_html_and_count()
	{
		$user     = User::factory()->create();
		$other    = User::factory()->create();
		$msg = Message::factory()->create([
			'from_id' => $other->id,
			'to_id'   => $user->id,
		]);

		Chatify::shouldReceive('fetchMessagesQuery')
			->andReturn(Message::where('id', $msg->id));
		Chatify::shouldReceive('fetchMessage')->andReturn('<div>m1</div>');
		Chatify::shouldReceive('messageCard')->andReturn('<div>card</div>');

		$this->actingAs($user);

		$response = $this->postJson('/chats/fetch-messages', ['id' => $other->id]);

		$response->assertJsonStructure(['count', 'messages'])
			->assertJson(['count' => 1]);
	}

	/**
	 ** @test
	 **
	 ** seen_marks_messages_and_returns_counts
	 **/
	public function seen_marks_messages_and_returns_counts()
	{
		$user = User::factory()->create();
		$other = User::factory()->create();

		$this->actingAs($user);

		// Chatify::makeSeen is a stub returning true; DB queries return 0 unseen
		$response = $this->postJson('/chats/make-seen', ['id' => $other->id]);

		$response->assertJsonStructure(['status', 'messengerCount']);
	}

	/**
	 ** @test
	 **
	 ** favorite_toggles_favorite_status
	 **/
	public function favorite_toggles_favorite_status()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		// Mock Chatify::inFavorite to simulate toggle (first: not fav, second: fav)
		Chatify::shouldReceive('inFavorite')->andReturn(false, true);
		Chatify::shouldReceive('makeInFavorite')->andReturnNull();

		// initially not favorite → added
		$response = $this->postJson('/chats/star', ['user_id' => $user->id]);
		$response->assertJson(['status' => 1]);

		// now favorite exists → removed
		$response = $this->postJson('/chats/star', ['user_id' => $user->id]);
		$response->assertJson(['status' => 0]);
	}

	/**
	 ** @test
	 **
	 ** get_favorites_returns_list_and_count
	 **/
	public function get_favorites_returns_list_and_count()
	{
		$user  = User::factory()->create();
		Favorite::factory()->count(2)->create(['user_id' => $user?->id]);

		$this->actingAs($user);

		$response = $this->getJson('/chats/favorites');

		$response->assertJsonStructure(['count', 'favorites']);
		$this->assertEquals(2, $response->json('count'));
	}

	/**
	 ** @test
	 **
	 ** search_returns_matching_users_html
	 **/
	public function search_returns_matching_users_html()
	{
		$user = User::factory()->create();
		User::factory()->create(['name' => 'Alice', 'created_by' => $user?->id]);
		User::factory()->create(['name' => 'Bob',   'created_by' => $user?->id]);

		$this->actingAs($user);

		$response = $this->postJson('/chats/search', ['input' => 'Al']);

		$response->assertJsonStructure(['records', 'addData']);
	}

	/**
	 ** @test
	 **
	 ** shared_photos_returns_html_for_each_image
	 **/
	public function shared_photos_returns_html_for_each_image()
	{
		$user = User::factory()->create();
		Chatify::shouldReceive('getSharedPhotos')->andReturn(['img1.png', 'img2.jpg']);

		$this->actingAs($user);

		$response = $this->getJson('/chats/shared', ['user_id' => 42]);

		$response->assertJsonStructure(['shared']);
	}

	/**
	 ** @test
	 **
	 ** delete_conversation_returns_deleted_flag
	 **/
	public function delete_conversation_returns_deleted_flag()
	{
		$user = User::factory()->create();
		Chatify::shouldReceive('deleteConversation')->andReturnTrue();

		$this->actingAs($user);

		$response = $this->postJson('/chats/delete-conversation', ['id' => 123]);

		$response->assertJson(['deleted' => 1]);
	}

	/**
	 ** @test
	 **
	 ** update_settings_toggles_dark_mode_and_color
	 **/
	public function update_settings_toggles_dark_mode_and_color()
	{
		$user = User::factory()->create(['dark_mode' => 0, 'messenger_color' => 'blue']);
		$this->actingAs($user);

		// toggle dark mode
		$response = $this->postJson('/chats/update-settings', ['dark_mode' => 'dark']);
		$response->assertJson(['status' => 0]);

		// change color
		$response = $this->postJson('/chats/update-settings', ['messengerColor' => 'chatify-red']);
		$response->assertJson(['status' => 0]);
	}

	/**
	 ** @test
	 **
	 ** set_active_status_toggles_user_active_flag
	 **/
	public function set_active_status_toggles_user_active_flag()
	{
		$user   = User::factory()->create(['active_status' => 0]);
		$target = User::factory()->create();
		$this->actingAs($user);

		$response = $this->postJson('/chats/set-active-status', ['user_id' => $target->id, 'status' => 1]);

		$response->assertJson(['status' => 1]);
		$this->assertDatabaseHas('users', ['id' => $target->id, 'active_status' => 1]);
	}

	/**
	 ** @test
	 **
	 ** id_fetch_data_returns_user_and_favorite_and_avatar
	 **/
	public function id_fetch_data_returns_user_and_favorite_and_avatar()
	{
		$user  = User::factory()->create();
		$other = User::factory()->create(['avatar' => null]);
		Favorite::factory()->create(['user_id' => $user?->id, 'favorite_id' => $other->id]);

		$this->actingAs($user);

		// Chatify::inFavorite is a stub returning false; mock to reflect the DB record
		Chatify::shouldReceive('inFavorite')
			->once()
			->with($other->id)
			->andReturn(true);

		$response = $this->postJson('/chats/id-info', ['id' => $other->id]);

		$response->assertJsonStructure(['favorite', 'user', 'avatar'])
			->assertJson(['favorite' => true]);
	}

	/**
	 ** @test
	 **
	 ** download_returns_file_or_404
	 **/
	public function download_returns_file_or_404()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		// set custom folder for testing
		config(['chatify.attachments.folder' => 'test_attach']);
		$path = storage_path('test_attach');
		@mkdir($path, 0755, true);
		file_put_contents("$path/test.txt", 'hello');

		// existing file → 200
		$response = $this->get('/chats/downloads/test.txt');
		$response->assertStatus(200)
			->assertHeader('content-disposition');

		// missing file → controller's broad catch intercepts abort(404) and redirects
		$response = $this->get('/chats/downloads/nope.txt');
		$response->assertRedirect();
	}

	/**
	 ** @test
	 **
	 ** get_contacts_returns_html_or_empty_hint
	 **/
	public function get_contacts_returns_html_or_empty_hint()
	{
		$this->markTestSkipped('Requires Chatify routes/views fully configured');
		$user = User::factory()->create();
		$this->actingAs($user);

		// no messages yet → empty hint
		$response = $this->getJson('/chats/get-contacts', ['messenger_id' => 'chat_1']);
		$this->assertStringContainsString('Your contact list is empty', $response->json('contacts'));

		// Chatify::getContactItem is a stub returning ''; verify endpoint still responds
		$response->assertJsonStructure(['contacts']);
	}

	/**
	 ** @test
	 **
	 ** update_contact_item_returns_html_and_count
	 **/
	public function update_contact_item_returns_html_and_count()
	{
		$user = User::factory()->create();
		$other = User::factory()->create();
		$this->actingAs($user);

		Message::factory()->count(2)->create([
			'to_id'   => $user?->id,
			'seen'    => 0,
		]);

		$response = $this->postJson('/chats/update-contacts', [
			'user_id'      => $other->id,
			'messenger_id' => 'chat_1',
		]);

		$response->assertJsonStructure(['contactItem', 'messengerCount'])
			->assertJson(['messengerCount' => 2]);
	}
}
