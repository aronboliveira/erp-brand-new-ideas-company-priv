@php
$fields ??= [];
	try {
		$fields = [
			[
				'name'     => 'date',
				'type'     => 'date',
				'label'    => __('Date'),
				'colClass' => VC::FM_GCB6,
				'attrs'    => ['class' => VC::FM_CT, 'required' => 'required'],
			],
			[
				'name'     => 'amount',
				'type'     => 'number',
				'label'    => __('Amount'),
				'colClass' => VC::FM_GCB6,
				'attrs'    => ['class' => VC::FM_CT, 'required' => 'required', 'step' => '0.01'],
			],
			[
				'name'     => 'description',
				'type'     => 'textarea',
				'label'    => __('Description'),
				'colClass' => VC::FM_GCB12,
				'attrs'    => ['class' => VC::FM_CT, 'rows' => 2],
			],
		];
	} catch (\Error $e) {
		Log::error('Error in debit_notes/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in debit_notes/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in debit_notes/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

@if(!empty($debitNote) && isset($debitNote->bill, $debitNote->id))
    @php
$billsEditDebitNoteBaseRouteName ??= '';
		$billsEditDebitNoteKebabRouteName ??= '';
		$billsEditDebitNoteResolvedName ??= null;
		$billIdValue ??= '';
		$debitNoteIdValue ??= '';
		$billsEditDebitNoteUrl ??= '#';
		$billsEditDebitNoteFormId ??= 'bills-edit-debit-note-form-x-x';
		$billsLangValue ??= 'en';
		$billsEditDebitNoteGuardMessage ??= '';
		try {
			$billsEditDebitNoteBaseRouteName = ViewsConstants::BIL . '.edit.debit.note';
			$billsEditDebitNoteKebabRouteName = Str::kebab($billsEditDebitNoteBaseRouteName);
			$billsEditDebitNoteResolvedName = Route::has($billsEditDebitNoteBaseRouteName)
				? $billsEditDebitNoteBaseRouteName
				: (Route::has($billsEditDebitNoteKebabRouteName) ? $billsEditDebitNoteKebabRouteName : null);
			$billIdValue = (string) data_get($debitNote ?? null, 'bill', '');
			$debitNoteIdValue = (string) data_get($debitNote ?? null, 'id', '');
			$billsEditDebitNoteUrl = ($billsEditDebitNoteResolvedName && $billIdValue !== '' && $debitNoteIdValue !== '')
				? (route($billsEditDebitNoteResolvedName, [$billIdValue, $debitNoteIdValue]) ?? '#')
				: '#';
			$billsEditDebitNoteFormId = 'bills-edit-debit-note-form-' . $billIdValue . '-' . $debitNoteIdValue;
			$billsLangValue = isset($lang) ? $lang : (Utility::fetchUserLang() ?? 'en');
			$billsEditDebitNoteGuardMessage = Utility::fetchLinkMessage($billsLangValue, ViewsConstants::BIL, 'edit_debit_note_route_unavailable')
				?? 'Edit debit note route is unavailable. Please contact technical support or your domain administrator.';
		} catch (\\Error $e) {
			FormLog::error('Error in debit_notes/edit.blade.php form @php block', [
				'exception_class' => get_class($e),
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
		} catch (\\Exception $e) {
			FormLog::error('Exception in debit_notes/edit.blade.php form @php block', [
				'exception_class' => get_class($e),
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
		} catch (\\Throwable $e) {
			FormLog::error('Throwable in debit_notes/edit.blade.php form @php block', [
				'exception_class' => get_class($e),
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
		}
@endphp

    {{ Form::model($debitNote, [
        'method'            => 'POST',
        'url'               => $billsEditDebitNoteUrl,
        'id'                => $billsEditDebitNoteFormId,
        'data-url'          => $billsEditDebitNoteUrl,
        'data-guard-msg'    => $billsEditDebitNoteGuardMessage,
        'data-sv-localized' => 'true',
    ]) }}
        <div class="modal-body">
            <div class="{{ VC::RW }}">
                @if(Utility::isFilled($fields) ?? [])
                    @foreach($fields as $f)
                        <div class="{{ $f['colClass'] }}">
                            {{ Form::label($f['name'], $f['label'], ['class' => VC::FM_LB]) }}

                            @if(($f['type'] ?? '') === 'textarea')
                                {{ Form::textarea($f['name'], null, $f['attrs']) }}
                            @else
                                @php
 $__method = $f['type'] ?? 'text';
@endphp
                                {!! call_user_func([Form::class, $__method], $f['name'], null, $f['attrs']) !!}
                            @endif
                        </div>
                    @endforeach
                @else
                    <div class="{{ VC::C12 }}">
                        <p class="{{ VC::TXT_MT }}">{{ __('No fields to display.') }}</p>
                    </div>
                @endif
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
            <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Update') }}</button>
        </div>
        <script defer>
            window.RouteGuard?.guardFormSubmit?.('{{ $billsEditDebitNoteFormId ?? "x" }}');
        </script>
    {{ Form::close() }}
@else
    <div class="modal-body">
        <p class="{{ VC::TXT_MT }}">{{ __('No debit note found to edit.') }}</p>
    </div>
    <div class="modal-footer">
        <button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">{{ __('Close') }}</button>
    </div>
@endif
