        @if($viewed)
            <div class="card">
                <h3>{{ $viewed->user->name }}
                    <span class="pill {{ $viewed->employment_status === 'active' ? 'pill-ok' : ($viewed->employment_status === 'on_notice' ? 'pill-warn' : 'pill-bad') }}" style="margin-left:8px;">
                        {{ ucfirst(str_replace('_',' ',$viewed->employment_status)) }}
                    </span>
                    <span class="pill pill-muted">{{ $viewed->user->roleLabel() }}</span>
                </h3>
                <p class="card-note">{{ $viewed->employee_code }} &middot; {{ $viewed->designation->title ?? '—' }} &middot; {{ $viewed->department->name ?? '—' }} &middot; {{ $viewed->city }}</p>

                <div class="tabs">
                    <button type="button" class="tab active" data-tab="personal" onclick="hrTab(this,'personal')">Personal</button>
                    <button type="button" class="tab" data-tab="employment" onclick="hrTab(this,'employment')">Employment</button>
                    <button type="button" class="tab" data-tab="bank" onclick="hrTab(this,'bank')">Bank & statutory</button>
                    <button type="button" class="tab" data-tab="documents" onclick="hrTab(this,'documents')">Documents</button>
                    @php $isSelf = $employee && $employee->id === $viewed->id; @endphp
                    @if(($role === 'hr_admin' || $role === 'super_admin') || $isSelf)
                        <button type="button" class="tab" data-tab="security" onclick="hrTab(this,'security')">Security</button>
                    @endif
                    @if(($role === 'hr_admin' || $role === 'super_admin') && $viewed->history && $viewed->history->isNotEmpty())
                        <button type="button" class="tab" data-tab="history" onclick="hrTab(this,'history')">History</button>
                    @endif
                </div>

                @php
                    $canEdit = ($role === 'hr_admin' || $role === 'super_admin') || ($employee && $employee->id === $viewed->id);
                    $isHr = ($role === 'hr_admin' || $role === 'super_admin');
                @endphp

                <form method="POST" action="{{ route('records.update', $viewed) }}">
                    @csrf
                    <div class="tabpanel active" data-tabpanel="personal">
                        <div class="field-grid">
                            <div class="field"><label>Phone</label><input name="phone" value="{{ $viewed->phone }}" @disabled(!$canEdit)></div>
                            <div class="field"><label>Personal email</label><input name="personal_email" value="{{ $viewed->personal_email }}" @disabled(!$canEdit)></div>
                            <div class="field full"><label>Address</label><input name="address" value="{{ $viewed->address }}" @disabled(!$canEdit)></div>
                            <div class="field"><label>City</label><input name="city" value="{{ $viewed->city }}" @disabled(!$canEdit)></div>
                            <div class="field"><label>Date of joining</label><input value="{{ $viewed->date_of_joining }}" disabled></div>
                        </div>
                    </div>

                    <div class="tabpanel" data-tabpanel="employment">
                        <div class="field-grid">
                            @if($isHr)
                                <div class="field">
                                    <label>Designation</label>
                                    <select name="designation_id">
                                        <option value="">—</option>
                                        @foreach($designations as $d)
                                            <option value="{{ $d->id }}" @selected($viewed->designation_id == $d->id)>{{ $d->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="field">
                                    <label>Department</label>
                                    <select name="department_id">
                                        <option value="">—</option>
                                        @foreach($departments as $d)
                                            <option value="{{ $d->id }}" @selected($viewed->department_id == $d->id)>{{ $d->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="field">
                                    <label>Reporting manager</label>
                                    <select name="reporting_manager_id">
                                        <option value="">— None —</option>
                                        @foreach($possibleManagers as $m)
                                            @continue($m->id === $viewed->id)
                                            <option value="{{ $m->id }}" @selected($viewed->reporting_manager_id == $m->id)>{{ $m->user->name }} ({{ $m->user->roleLabel() }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="field">
                                    <label>Employment status</label>
                                    <select name="employment_status">
                                        <option value="active" @selected($viewed->employment_status === 'active')>Active</option>
                                        <option value="on_notice" @selected($viewed->employment_status === 'on_notice')>On notice</option>
                                        <option value="exited" @selected($viewed->employment_status === 'exited')>Exited</option>
                                    </select>
                                </div>
                                <div class="field">
                                    <label>Portal role</label>
                                    <select name="portal_role">
                                        <option value="employee" @selected($viewed->user->role === 'employee')>Employee</option>
                                        <option value="manager" @selected($viewed->user->role === 'manager')>Reporting Manager</option>
                                        @if(in_array($viewed->user->role, ['hr_admin','super_admin']))
                                            <option value="{{ $viewed->user->role }}" selected disabled>{{ $viewed->user->roleLabel() }} (change via System & Access)</option>
                                        @endif
                                    </select>
                                    <p class="field-hint">Promotions to HR Admin / Super Admin are made from System & Access.</p>
                                </div>
                            @else
                                <div class="field"><label>Designation</label><input value="{{ $viewed->designation->title ?? '—' }}" disabled></div>
                                <div class="field"><label>Department</label><input value="{{ $viewed->department->name ?? '—' }}" disabled></div>
                                <div class="field"><label>Reporting manager</label><input value="{{ $viewed->reportingManager->user->name ?? '—' }}" disabled></div>
                                <div class="field"><label>Employment status</label><input value="{{ ucfirst(str_replace('_',' ',$viewed->employment_status)) }}" disabled></div>
                            @endif
                        </div>
                        @if(!$isHr)
                            <p class="field-hint" style="margin-top:14px;">Designation, department and manager are HR-controlled and not editable from this form.</p>
                        @endif
                    </div>

                    <div class="tabpanel" data-tabpanel="bank">
                        <div class="field-grid">
                            <div class="field"><label>Bank name</label><input name="bank_name" value="{{ $viewed->bank_name }}" @disabled(!$canEdit)></div>
                            <div class="field"><label>Account number</label><input name="bank_account_number" value="{{ $viewed->bank_account_number }}" @disabled(!$canEdit)></div>
                            <div class="field"><label>IFSC</label><input name="bank_ifsc" value="{{ $viewed->bank_ifsc }}" @disabled(!$canEdit)></div>
                        </div>
                    </div>

                    @if($canEdit)
                        <div class="form-actions"><button type="submit" class="btn-primary">Save changes</button></div>
                    @endif
                </form>

                <div class="tabpanel" data-tabpanel="documents">
                    @if(($viewed->documents ?? collect())->isEmpty())
                        <p class="field-hint">No documents on file.</p>
                    @else
                        <table>
                            <thead><tr><th>Document</th><th>Uploaded</th><th>Expiry</th><th>Status</th><th></th></tr></thead>
                            <tbody>
                                @foreach($viewed->documents as $doc)
                                    @php
                                        $expiringSoon = $doc->expiry_date && \Illuminate\Support\Carbon::parse($doc->expiry_date)->lte(now()->addDays(30));
                                        $docPill = ['verified' => 'pill-ok', 'pending' => 'pill-warn', 'rejected' => 'pill-bad'][$doc->status] ?? 'pill-muted';
                                    @endphp
                                    <tr>
                                        <td>{{ $doc->document_type }}</td>
                                        <td>{{ \Illuminate\Support\Carbon::parse($doc->uploaded_at)->format('d M Y') }}</td>
                                        <td>
                                            {{ $doc->expiry_date ? \Illuminate\Support\Carbon::parse($doc->expiry_date)->format('d M Y') : '—' }}
                                            @if($expiringSoon)<span class="pill pill-warn" style="margin-left:4px;">Expiring soon</span>@endif
                                        </td>
                                        <td><span class="pill {{ $docPill }}">{{ ucfirst($doc->status) }}</span></td>
                                        <td>
                                            <div class="row-actions">
                                                <a href="{{ route('records.document.download', $doc) }}" class="btn-ghost">Download</a>
                                                @if(($role === 'hr_admin' || $role === 'super_admin') && $doc->status === 'pending')
                                                    <form method="POST" action="{{ route('records.document.verify', $doc) }}">@csrf<input type="hidden" name="action" value="verified"><button class="approve" type="submit">Verify</button></form>
                                                    <form method="POST" action="{{ route('records.document.verify', $doc) }}">@csrf<input type="hidden" name="action" value="rejected"><button class="reject" type="submit">Reject</button></form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    @if(($role === 'hr_admin' || $role === 'super_admin') || $isSelf)
                        <form method="POST" action="{{ route('records.document.upload', $viewed) }}" enctype="multipart/form-data" style="margin-top:20px;padding-top:20px;border-top:1px solid var(--line);">
                            @csrf
                            <div class="field-grid cols-3">
                                <div class="field">
                                    <label>Document type</label>
                                    <select name="document_type" required>
                                        <option value="Aadhar Card">Aadhar Card</option>
                                        <option value="PAN Card">PAN Card</option>
                                        <option value="Address Proof">Address Proof</option>
                                        <option value="Education Certificate">Education Certificate</option>
                                        <option value="Experience Letter">Experience Letter</option>
                                        <option value="Offer Letter">Offer Letter</option>
                                        <option value="Resume">Resume</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="field"><label>File (PDF/JPG/PNG, max 5MB)</label><input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png" required></div>
                                <div class="field"><label>Expiry date (if applicable)</label><input type="date" name="expiry_date"></div>
                            </div>
                            <div class="form-actions"><button type="submit" class="btn-primary">Upload document</button></div>
                        </form>
                    @endif
                </div>

                @if(($role === 'hr_admin' || $role === 'super_admin') || $isSelf)
                    <div class="tabpanel" data-tabpanel="security">
                        @if($isSelf)
                            <p class="card-note">Change your own sign-in password.</p>
                            <form method="POST" action="{{ route('records.password.update', $viewed) }}" style="max-width:360px;">
                                @csrf
                                <div class="field" style="margin-bottom:14px;"><label>Current password</label><input type="password" name="current_password" required></div>
                                <div class="field" style="margin-bottom:14px;"><label>New password</label><input type="password" name="new_password" minlength="8" required></div>
                                <div class="field"><label>Confirm new password</label><input type="password" name="new_password_confirmation" minlength="8" required></div>
                                <div class="form-actions"><button type="submit" class="btn-primary">Update password</button></div>
                            </form>
                        @else
                            <p class="card-note">Reset {{ $viewed->user->name }}'s password — no current password required for an admin reset.</p>
                            <form method="POST" action="{{ route('records.password.update', $viewed) }}" style="max-width:360px;">
                                @csrf
                                <div class="field" style="margin-bottom:14px;"><label>New password</label><input type="password" name="new_password" minlength="8" required></div>
                                <div class="field"><label>Confirm new password</label><input type="password" name="new_password_confirmation" minlength="8" required></div>
                                <div class="form-actions"><button type="submit" class="btn-primary">Reset password</button></div>
                            </form>
                        @endif
                    </div>
                @endif

                @if(($role === 'hr_admin' || $role === 'super_admin') && $viewed->history && $viewed->history->isNotEmpty())
                    <div class="tabpanel" data-tabpanel="history">
                        <table>
                            <thead><tr><th>Field</th><th>From</th><th>To</th><th>Effective</th></tr></thead>
                            <tbody>
                                @foreach($viewed->history->sortByDesc('id') as $h)
                                    <tr>
                                        <td>{{ $h->field_changed }}</td>
                                        <td>{{ $h->old_value ?? '—' }}</td>
                                        <td>{{ $h->new_value ?? '—' }}</td>
                                        <td>{{ $h->effective_date }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @else
            <p class="field-hint">No employee profile linked to your account.</p>
        @endif
