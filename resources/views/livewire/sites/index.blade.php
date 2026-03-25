<div class="space-y-8">
    <div>
        <h1 class="text-3xl font-semibold">Sites</h1>
        <p class="mt-2 text-sm text-zinc-600">Track repositories, sync status, and deployment metadata.</p>
    </div>

    @if (session('status'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            <ul class="list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-zinc-200">
        <h2 class="text-lg font-medium">Add site</h2>

        <form method="POST" action="{{ route('sites.store') }}" class="mt-4 grid gap-4 md:grid-cols-3">
            @csrf
            <input name="name" type="text" value="{{ old('name') }}" placeholder="Project name" class="rounded-lg border border-zinc-300 px-3 py-2" required />
            <input name="repo_url" type="url" value="{{ old('repo_url') }}" placeholder="https://github.com/owner/repo" class="rounded-lg border border-zinc-300 px-3 py-2" required />
            <input name="repo_branch" type="text" value="{{ old('repo_branch', 'main') }}" placeholder="main" class="rounded-lg border border-zinc-300 px-3 py-2" />
            <button type="submit" class="rounded-lg bg-zinc-900 px-4 py-2 text-white md:col-span-3 md:w-fit">Queue repository</button>
        </form>
    </section>

    <section class="rounded-2xl bg-white shadow-sm ring-1 ring-zinc-200">
        <div class="border-b border-zinc-200 px-6 py-4">
            <h2 class="text-lg font-medium">Tracked sites</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 text-sm">
                <thead class="bg-zinc-50">
                    <tr>
                        <th class="px-6 py-3 text-left font-medium text-zinc-600">Name</th>
                        <th class="px-6 py-3 text-left font-medium text-zinc-600">Type</th>
                        <th class="px-6 py-3 text-left font-medium text-zinc-600">Branch</th>
                        <th class="px-6 py-3 text-left font-medium text-zinc-600">Status</th>
                        <th class="px-6 py-3 text-left font-medium text-zinc-600">Last sync</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 bg-white">
                    @forelse ($sites as $site)
                        <tr>
                            <td class="px-6 py-4">
                                <div class="font-medium text-zinc-900">{{ $site->name }}</div>
                                <div class="text-zinc-500">{{ $site->repo_url }}</div>
                            </td>
                            <td class="px-6 py-4 text-zinc-700">{{ $site->project_type ?? 'pending' }}</td>
                            <td class="px-6 py-4 text-zinc-700">{{ $site->repo_branch }}</td>
                            <td class="px-6 py-4 text-zinc-700">
                                <span class="rounded-full bg-zinc-100 px-2 py-1 text-xs font-medium uppercase tracking-wide">{{ $site->status }}</span>
                            </td>
                            <td class="px-6 py-4 text-zinc-500">{{ optional($site->last_synced_at)->diffForHumans() ?? 'never' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-zinc-500">No sites added yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
