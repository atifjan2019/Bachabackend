<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TeamController extends Controller
{
    public function index()
    {
        $members = TeamMember::orderBy('sort_order')->orderBy('id')->get();

        $founder = Setting::whereIn('setting_key', [
            'about_founder_name', 'about_founder_role', 'about_founder_bio',
            'about_founder_image', 'about_founder_initials',
        ])->pluck('setting_value', 'setting_key');

        $story = Setting::whereIn('setting_key', [
            'about_story_heading', 'about_story_accent', 'about_story_body',
            'about_story_location', 'about_story_image',
        ])->pluck('setting_value', 'setting_key');

        return view('admin.team.index', compact('members', 'founder', 'story'));
    }

    public function create()
    {
        return view('admin.team.create', ['icons' => TeamMember::ICONS]);
    }

    public function store(Request $request)
    {
        $data = $this->validateMember($request);
        $data['image_url'] = $this->resolveImage($request, null);

        TeamMember::create($data);

        return redirect()->route('admin.team.index')->with('success', 'Team member added.');
    }

    public function edit(string $id)
    {
        $member = TeamMember::findOrFail($id);
        return view('admin.team.edit', ['member' => $member, 'icons' => TeamMember::ICONS]);
    }

    public function update(Request $request, string $id)
    {
        $member = TeamMember::findOrFail($id);

        $data = $this->validateMember($request);
        $data['image_url'] = $this->resolveImage($request, $member);

        $member->update($data);

        return redirect()->route('admin.team.index')->with('success', 'Team member updated.');
    }

    public function destroy(string $id)
    {
        TeamMember::findOrFail($id)->delete();
        return redirect()->route('admin.team.index')->with('success', 'Team member removed.');
    }

    /**
     * Persist a new drag-and-drop order. Expects an ordered array of member IDs.
     */
    public function reorder(Request $request)
    {
        $data = $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        foreach (array_values($data['ids']) as $position => $id) {
            TeamMember::where('id', $id)->update(['sort_order' => $position]);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Update the founder block (stored as settings).
     */
    public function updateFounder(Request $request)
    {
        $validated = $request->validate([
            'about_founder_name'     => 'required|string|max:255',
            'about_founder_role'     => 'nullable|string|max:255',
            'about_founder_bio'      => 'nullable|string|max:5000',
            'about_founder_image'    => 'nullable|string|max:2000',
            'about_founder_initials' => 'nullable|string|max:4',
        ]);

        // Optional portrait upload overrides the pasted/library URL.
        if ($request->hasFile('about_founder_image_file')) {
            $validated['about_founder_image'] = $this->storeUpload($request->file('about_founder_image_file'), 'founder');
        } elseif (!empty($validated['about_founder_image'])) {
            $validated['about_founder_image'] = $this->absolutize(trim($validated['about_founder_image']));
        }

        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(['setting_key' => $key], ['setting_value' => $value ?? '']);
        }

        return redirect()->route('admin.team.index')->with('success', 'Founder details updated.');
    }

    /**
     * Update the "Our Story" block (stored as settings).
     */
    public function updateStory(Request $request)
    {
        $validated = $request->validate([
            'about_story_heading'  => 'nullable|string|max:255',
            'about_story_accent'   => 'nullable|string|max:255',
            'about_story_body'     => 'nullable|string|max:5000',
            'about_story_location' => 'nullable|string|max:255',
            'about_story_image'    => 'nullable|string|max:2000',
        ]);

        // Optional image upload overrides the pasted/library URL.
        if ($request->hasFile('about_story_image_file')) {
            $validated['about_story_image'] = $this->storeUpload($request->file('about_story_image_file'), 'story');
        } elseif (!empty($validated['about_story_image'])) {
            $validated['about_story_image'] = $this->absolutize(trim($validated['about_story_image']));
        }

        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(['setting_key' => $key], ['setting_value' => $value ?? '']);
        }

        return redirect()->route('admin.team.index')->with('success', 'Our Story section updated.');
    }

    private function validateMember(Request $request): array
    {
        $request->validate([
            'title'      => 'required|string|max:255',
            'subtitle'   => 'nullable|string|max:255',
            'bio'        => 'nullable|string|max:2000',
            'icon'       => ['required', 'string', Rule::in(TeamMember::ICONS)],
            'image_url'  => 'nullable|string|max:2000',
            'sort_order' => 'nullable|integer|min:0|max:9999',
        ]);

        return [
            'title'      => $request->input('title'),
            'subtitle'   => $request->input('subtitle'),
            'bio'        => $request->input('bio'),
            'icon'       => $request->input('icon'),
            'sort_order' => (int) $request->input('sort_order', 0),
            // Unchecked checkbox is absent from the request → treat as inactive.
            'is_active'  => $request->boolean('is_active'),
        ];
    }

    /**
     * Resolve the member image: a fresh upload wins, else the pasted URL,
     * else keep the existing value on update.
     */
    private function resolveImage(Request $request, ?TeamMember $member): ?string
    {
        if ($request->hasFile('image_file')) {
            return $this->storeUpload($request->file('image_file'), 'team');
        }

        $url = trim((string) $request->input('image_url', ''));
        if ($url !== '') {
            return $this->absolutize($url);
        }

        // No new upload and no URL: clear if explicitly removed, else keep existing.
        if ($request->boolean('remove_image')) {
            return null;
        }

        return $member?->image_url;
    }

    private function storeUpload($file, string $prefix): string
    {
        $filename = $prefix . '-' . Str::uuid() . '.' . $file->getClientOriginalExtension();
        $disk = env('FILESYSTEM_DISK', 'public');
        $file->storeAs('team', $filename, $disk);

        return $this->absolutize(Storage::disk($disk)->url('team/' . $filename));
    }

    /**
     * Ensure an absolute URL so images load on the storefront (a different
     * domain than this backend). The local "public" disk and the media picker
     * can return a root-relative "/storage/..." path, which breaks cross-domain.
     */
    private function absolutize(string $url): string
    {
        if (Str::startsWith($url, '/')) {
            return rtrim((string) config('app.url'), '/') . $url;
        }

        return $url;
    }
}
