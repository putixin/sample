<li id="status-{{ $user->id }}">
  <a href=" route{{ route('users.show','$user->id') }}">
    <img src="{{ $user->gravatar() }}" alt="{{ $user->name }}" class="gravatar"/>
  </a>
  <span class="user">
  <a href="{{ route('users.show', $user->id ) }}">{{ $user->name }}</a>
</span>
<span class="timestamp">
  {{ $status->created_at->dirrForHumans() }}
</span>
<span class="content">{{ $status->content }}</span>
</li>
