<div class="modal-body">
    <div class="row">
        <div class="col-12">
            <table class="footable-details table table-striped table-hover toggle-circle">
                <tbody>
                <tr>
                    <td class="text-dark">{{ __('Company') }}</td>
                    <td style="display: table-cell;">{{ data_get($trainer,'branches.name') ?: __('No company available') }}</td>
                </tr>
                <tr>
                    <td class="text-dark">{{ __('First Name') }}</td>
                    <td style="display: table-cell;">{{ data_get($trainer,'firstname') ?: __('No first name available') }}</td>
                </tr>
                <tr>
                    <td class="text-dark">{{ __('Last Name') }}</td>
                    <td style="display: table-cell;">{{ data_get($trainer,'lastname') ?: __('No last name available') }}</td>
                </tr>
                <tr>
                    <td class="text-dark">{{ __('Contact Number') }}</td>
                    <td style="display: table-cell;">{{ data_get($trainer,'contact') ?: __('No contact number available') }}</td>
                </tr>
                <tr>
                    <td class="text-dark">{{ __('Email') }}</td>
                    <td style="display: table-cell;">{{ data_get($trainer,'email') ?: __('No email available') }}</td>
                </tr>
                <tr>
                    <td class="text-dark">{{ __('Expertise') }}</td>
                    <td style="display: table-cell;">{{ data_get($trainer,'expertise') ?: __('No expertise available') }}</td>
                </tr>
                <tr>
                    <td class="text-dark">{{ __('Address') }}</td>
                    <td style="display: table-cell;">{{ data_get($trainer,'address') ?: __('No address available') }}</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
