@extends("layouts.app")

@section("title", "Posts")

@section("content")

@auth
@if(in_array(auth()->user()->role, ['admin','editor','contributor']))
<a href="{{ route('posts.create') }}" class="btn btn-primary mb-3">
    Add New Post
</a>
@endif
@endauth

<table class="table table-bordered table-hover">
    <thead>
        <tr>
            <th>លេខរៀង</th>
            <th>Title</th>
            <th>Category</th>
            <th>Author</th>
            <th>Image</th>
            <th>Content</th>
            <th>Status</th>
            <th>សកម្មភាព</th>
        </tr>
    </thead>

    <tbody>
        @forelse ($posts as $post)
            <tr>
                <td>{{ $loop->iteration }}</td>

                <td>{{ $post->title }}</td>

                <td>{{ $post->category->name ?? 'No Category' }}</td>

                <td>{{ $post->user->name ?? 'Guest' }}</td>

                <td>
                    <img src="{{ $post->image && file_exists(public_path('storage/' . $post->image))
                        ? asset('storage/' . $post->image)
                        : asset('images/no_image.png') }}"
                        alt="{{ $post->title }}"
                        width="50"
                        height="50">
                </td>
                <td>
                    {{ $post->content ?? 'No Content' }}
                </td>

                <td>
                    <span class="badge
                        {{ $post->status == 'published' ? 'bg-primary'
                        : ($post->status == 'draft' ? 'bg-warning' : 'bg-secondary') }}">
                        {{ ucfirst($post->status) }}
                    </span>
                </td>

                <td>
                    <a href="{{ route('posts.show', $post->id) }}"
                        class="btn btn-info btn-sm">Detail</a>

                        @auth

                        {{-- EDIT --}}
                        @if(in_array(auth()->user()->role, ['admin','editor'])
                            || auth()->id() === $post->user_id)

                        <a href="{{ route('posts.edit', $post->id) }}"
                           class="btn btn-warning btn-sm">
                            Edit
                        </a>

                        @endif


                        {{-- DELETE (ADMIN ONLY) --}}
                        @if(auth()->user()->role === 'admin')

                        <form action="{{ route('posts.destroy', $post->id) }}"
                              method="POST"
                              class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm">
                                Delete
                            </button>
                        </form>

                        @endif

                        @endauth

                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center">
                    មិនមានទិន្នន័យ
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

<div class="d-flex justify-content-center">
    {{ $posts->links() }}
</div>

@endsection
