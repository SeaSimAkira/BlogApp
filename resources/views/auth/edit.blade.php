
@extends("layouts.app")
@section("title", "Edit User")
@push("styles")
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    body { font-family: 'Inter', sans-serif; background-color: #f4f7f9; color: #334155; }

    .user-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 1.25rem;
        box-shadow: 0 5px 15px rgba(0,0,0,0.03);
    }

    /* Avatar Upload Circle */
    .avatar-upload {
        position: relative;
        max-width: 150px;
        margin: 0 auto 2rem;
    }
    .avatar-preview {
        width: 150px;
        height: 150px;
        position: relative;
        border-radius: 100%;
        border: 6px solid #f8fafc;
        box-shadow: 0px 2px 10px 0px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }
    .avatar-preview img { width: 100%; height: 100%; object-fit: cover; }

    .avatar-edit {
        position: absolute;
        right: 5px;
        bottom: 5px;
        z-index: 1;
    }
    .avatar-edit label {
        display: inline-block;
        width: 34px;
        height: 34px;
        margin-bottom: 0;
        border-radius: 100%;
        background: #4361ee;
        color: #fff;
        border: 1px solid transparent;
        box-shadow: 0px 2px 4px 0px rgba(0, 0, 0, 0.12);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease-in-out;
    }
    .avatar-edit label:hover { background: #3a0ca3; }

    /* Role Choice Cards */
    .role-card input[type="radio"] { display: none; }
    .role-card label {
        display: block;
        padding: 1rem;
        border: 2px solid #f1f5f9;
        border-radius: 0.75rem;
        cursor: pointer;
        transition: all 0.2s;
    }
    .role-card input[type="radio"]:checked + label {
        border-color: #4361ee;
        background-color: #f0f7ff;
    }

    /* Toggle Switch */
    .form-check-input:checked { background-color: #10b981; border-color: #10b981; }
</style>
@endpush
@section("content")
<div class="container-fluid py-5">

    <form action="{{ route('users.update', $user->id) }}"
          method="POST"
          enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row justify-content-center">

            <!-- Left: Profile Info -->
            <div class="col-lg-8">
                <div class="user-card p-4 p-md-5 mb-4">

                    <div class="text-center mb-5">
                        <h3 class="fw-bold">Edit User Profile</h3>
                        <p class="text-muted">Update user information</p>
                    </div>

                    <!-- Profile Picture -->
                    <div class="avatar-upload">
                        <div class="avatar-edit">
                            <input type="file"
                                   name="profile_picture"
                                   id="imageUpload"
                                   accept=".png,.jpg,.jpeg"
                                   class="d-none"/>
                            <label for="imageUpload">
                                <i class="bi bi-camera-fill"></i>
                            </label>
                        </div>

                        <div class="avatar-preview">
                            <img id="imagePreview"
                                 src="{{ $user->profile_picture
                                        ? asset('storage/'.$user->profile_picture)
                                        : 'https://ui-avatars.com/api/?name='.$user->name }}"
                                 alt="Profile">
                        </div>
                    </div>

                    <div class="row g-4">

                        <!-- Name -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Full Name</label>
                            <input type="text"
                                   name="name"
                                   class="form-control"
                                   value="{{ old('name', $user->name) }}"
                                   required>
                        </div>

                        <!-- Email -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email Address</label>
                            <input type="email"
                                   name="email"
                                   class="form-control"
                                   value="{{ old('email', $user->email) }}"
                                   required>
                        </div>

                        <!-- Password -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Password (Leave blank to keep old)</label>
                            <div class="input-group">
                                <input type="password" name="password" id="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       placeholder="Input Password" >
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password">
                                    <i class="bi bi-eye"></i> </button>
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Confirm Password</label>
                            <div class="input-group">
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                       class="form-control" placeholder="Retype You Password Again" >
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password_confirmation">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Right Side -->
            <div class="col-lg-4">

                <!-- Status -->
                <div class="user-card p-4 mb-4 text-center">
                    <label class="form-label fw-bold small text-muted">
                        Account Status
                    </label>

                    <div class="form-check form-switch d-flex justify-content-center gap-3 ps-0">
                        <span class="text-muted small">Inactive</span>

                        <input class="form-check-input ms-0"
                               type="checkbox"
                               name="is_active"
                               value="1"
                               {{ $user->is_active ? 'checked' : '' }}
                               style="width:3rem;height:1.5rem;">

                        <span class="fw-bold text-success">Active</span>
                    </div>
                </div>

                <!-- Role -->
                <div class="user-card p-4 mb-4">
                    <label class="form-label fw-bold small text-muted">
                        Assign Role
                    </label>

                    @foreach(['admin'=>'Admin','editor'=>'Editor','contributor'=>'Contributor','viewer'=>'Viewer'] as $val=>$label)
                    <div class="role-card mb-2">
                        <input type="radio"
                               name="role"
                               id="role_{{ $val }}"
                               value="{{ $val }}"
                               {{ $user->role == $val ? 'checked' : '' }}>

                        <label for="role_{{ $val }}">
                            <span class="fw-bold">{{ $label }}</span>
                        </label>
                    </div>
                    @endforeach
                </div>

                <div class="d-grid">
                    <button type="submit"
                            class="btn btn-primary btn-lg rounded-pill fw-bold">
                        Update User
                    </button>

                    <a href="{{ route('users.index') }}"
                       class="btn btn-link text-muted small">
                       Cancel
                    </a>
                </div>

            </div>
        </div>
    </form>
</div>
@endsection
@push("scripts")
<script>
    // Profile Picture Preview
    document.getElementById('imageUpload').addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('imagePreview').src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    });
</script>
<script>
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            // Find the input associated with this button
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const icon = this.querySelector('i');

            // Toggle the type attribute
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash'); // Switch icon
            } else {
                input.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye'); // Switch back
            }
        });
    });
    </script>
@endpush