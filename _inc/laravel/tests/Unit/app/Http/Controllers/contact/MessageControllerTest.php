<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\{ChMessage as Message, ChFavorite as Favorite, User};
use Chatify\Facades\ChatifyMessenger as Chatify;
use Illuminate\{Foundation\Testing\RefreshDatabase, Support\Facades\File};

class MessagesControllerTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** pusher_auth should return 401 for unauthenticated requests
	 **/
	public function test_pusher_auth_rejects_guests()
	{
		$response = $this->post('/chatify/pusher/auth', [
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

		$response = $this->post('/chatify/pusher/auth', [
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
		$response = $this->get('/chatify');
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

		$response = $this->get('/chatify');
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
		$user = User::factory()->create(['type' => 'company']);
		$this->actingAs($user);

		$response = $this->get('/chatify');
		$response->assertStatus(200);
		$response->assertViewIs('Chatify::pages.app');
	}

	/**
	 ** @test
	 **
	 ** id_fetch_data should reject guests
	 **/
	public function test_id_fetch_data_rejects_guests()
	{
		$other = User::factory()->create();

		$response = $this->post('/chatify/idInfo', ['id' => $other->id]);

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

		$response = $this->postJson('/chatify/idInfo', ['id' => $other->id]);

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

		$response = $this->get('/chatify/download/nonexistent.txt');

		$response->assertStatus(404);
	}

	/**
	 ** @test
	 **
	 ** download serves the file when it exists
	 **/
	public function test_download_serves_existing_file()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		config(['chatify.attachments.folder' => 'test_attach']);
		$path = storage_path('test_attach');
		File::ensureDirectoryExists($path);
		file_put_contents("$path/example.txt", 'hello world');

		$response = $this->get('/chatify/download/example.txt');

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

		$response = $this->post(action(
			[\App\Http\Controllers\vendor\Chatify\MessagesController::class, 'pusherAuth']
		), [
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
		$response = $this->post(action(
			[\App\Http\Controllers\vendor\Chatify\MessagesController::class, 'pusherAuth']
		), [
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
		$user = User::factory()->create(['type' => 'user']);
		$this->actingAs($user);

		$response = $this->get(action(
			[\App\Http\Controllers\vendor\Chatify\MessagesController::class, 'index'],
			['id' => null]
		));

		$response->assertStatus(200)
			->assertViewIs('Chatify::pages.app');
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

		$response = $this->get(action(
			[\App\Http\Controllers\vendor\Chatify\MessagesController::class, 'index'],
			['id' => null]
		));

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

		Chatify::shouldReceive('newMessage')->once();
		Chatify::shouldReceive('fetchMessage')->andReturn('<p>msg</p>');
		Chatify::shouldReceive('push')->once();

		$response = $this->postJson(action(
			[\App\Http\Controllers\vendor\Chatify\MessagesController::class, 'send']
		), [
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
		Message::factory()->create([
			'id'      => 1,
			'from_id' => $other->id,
			'to_id'   => $user?->id,
		]);

		Chatify::shouldReceive('fetchMessage')->andReturn('<div>m1</div>');

		$this->actingAs($user);

		$response = $this->postJson(action(
			[\App\Http\Controllers\vendor\Chatify\MessagesController::class, 'fetch']
		), ['id' => $other->id]);

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

		Message::factory()->count(3)->create([
			'from_id' => $other->id,
			'to_id'   => $user?->id,
			'seen'    => 0,
		]);

		$this->actingAs($user);

		$response = $this->postJson(action(
			[\App\Http\Controllers\vendor\Chatify\MessagesController::class, 'seen']
		), ['id' => $other->id]);

		$response->assertJsonStructure(['status', 'messengerCount']);
		$this->assertDatabaseCount('ch_messages', 3);
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

		// initially not favorite
		$response = $this->postJson(action(
			[\App\Http\Controllers\vendor\Chatify\MessagesController::class, 'favorite']
		), ['user_id' => 999]);
		$response->assertJson(['status' => 1]);

		// now favorite exists
		$response = $this->postJson(action(
			[\App\Http\Controllers\vendor\Chatify\MessagesController::class, 'favorite']
		), ['user_id' => 999]);
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

		$response = $this->getJson(action(
			[\App\Http\Controllers\vendor\Chatify\MessagesController::class, 'getFavorites']
		));

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

		$response = $this->postJson(action(
			[\App\Http\Controllers\vendor\Chatify\MessagesController::class, 'search']
		), ['input' => 'Al']);

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

		$response = $this->getJson(action(
			[\App\Http\Controllers\vendor\Chatify\MessagesController::class, 'sharedPhotos']
		), ['user_id' => 42]);

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

		$response = $this->postJson(action(
			[\App\Http\Controllers\vendor\Chatify\MessagesController::class, 'deleteConversation']
		), ['id' => 123]);

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
		$response = $this->postJson(action(
			[\App\Http\Controllers\vendor\Chatify\MessagesController::class, 'updateSettings']
		), ['dark_mode' => 'dark']);
		$response->assertJson(['status' => 0]);

		// change color
		$response = $this->postJson(action(
			[\App\Http\Controllers\vendor\Chatify\MessagesController::class, 'updateSettings']
		), ['messengerColor' => 'chatify-red']);
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

		$response = $this->postJson(action(
			[\App\Http\Controllers\vendor\Chatify\MessagesController::class, 'setActiveStatus']
		), ['user_id' => $target->id, 'status' => 1]);

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

		$response = $this->postJson(action(
			[\App\Http\Controllers\vendor\Chatify\MessagesController::class, 'idFetchData']
		), ['id' => $other->id]);

		$response->assertJsonStructure(['favorite', 'user', 'avatar'])
			->assertJson(['favorite' => 1]);
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

		// write a temp file
		$path    = storage_path('app/attachments/test.txt');
		@mkdir(dirname($path), 0755, true);
		file_put_contents($path, 'hello');

		// existing
		$response = $this->get(action(
			[\App\Http\Controllers\vendor\Chatify\MessagesController::class, 'download'],
			['fileName' => 'test.txt']
		));
		$response->assertStatus(200)
			->assertHeader('content-disposition');

		// missing
		$response = $this->get(action(
			[\App\Http\Controllers\vendor\Chatify\MessagesController::class, 'download'],
			['fileName' => 'nope.txt']
		));
		$response->assertStatus(404);
	}

	/**
	 ** @test
	 **
	 ** get_contacts_returns_html_or_empty_hint
	 **/
	public function get_contacts_returns_html_or_empty_hint()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		// no messages yet → empty hint
		$response = $this->getJson(action(
			[\App\Http\Controllers\vendor\Chatify\MessagesController::class, 'getContacts']
		), ['messenger_id' => 'chat_1']);
		$this->assertStringContainsString('Your contact list is empty', $response->json('contacts'));

		// after one message
		$other = User::factory()->create();
		Message::factory()->create([
			'from_id' => $user?->id,
			'to_id'   => $other->id,
		]);
		$response = $this->getJson(action(
			[\App\Http\Controllers\vendor\Chatify\MessagesController::class, 'getContacts']
		), ['messenger_id' => 'chat_1']);
		$this->assertStringNotContainsString('empty', $response->json('contacts'));
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

		$response = $this->postJson(action(
			[\App\Http\Controllers\vendor\Chatify\MessagesController::class, 'updateContactItem']
		), [
			'user_id'      => $other->id,
			'messenger_id' => 'chat_1',
		]);

		$response->assertJsonStructure(['contactItem', 'messengerCount'])
			->assertJson(['messengerCount' => 2]);
	}
}
