<?php

namespace App\Http\Controllers;

use App\Models\FaceWidget;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule; // ← ADD THIS

class FaceWidgetController extends Controller
{
    public function index()
    {
        $widgets = FaceWidget::orderBy('created_at', 'desc')->paginate(10);
        return view('widgets.index', compact('widgets'));
    }

    public function create()
    {
        return view('widgets.create', [
            'widget' => new FaceWidget(),
            'method' => 'POST',
            'action' => route('widgets.store'),
        ]);
    }

    public function store(Request $request)
    {
        // Pass null — no ID to ignore, name must be unique across all rows
        $data = $this->validated($request, null);
        $data['id'] = (string) Str::uuid();

        $widget = FaceWidget::create($data);

        return redirect()->route('widgets.show', $widget->id)
            ->with('success', 'Widget created successfully.');
    }

    public function show(FaceWidget $widget)
    {
        return view('widgets.show', compact('widget'));
    }

    public function edit(FaceWidget $widget)
    {
        return view('widgets.edit', [
            'widget' => $widget,
            'method' => 'PUT',
            'action' => route('widgets.update', $widget->id),
        ]);
    }

    public function update(Request $request, FaceWidget $widget)
    {
        // Pass the current widget's ID so the unique rule ignores its own row.
        // Without this, editing a widget without changing the name would fail
        // because the name already exists — belonging to itself.
        $data = $this->validated($request, $widget->id);
        $widget->update($data);

        return redirect()->route('widgets.show', $widget->id)
            ->with('success', 'Widget updated successfully.');
    }

    public function destroy(FaceWidget $widget)
    {
        $widget->delete();
        return redirect()->route('widgets.index')
            ->with('success', 'Widget deleted successfully.');
    }

    /**
     * @param  string|null  $ignoreId  Pass the current widget UUID when updating
     *                                  so the unique rule doesn't flag its own name.
     */
    private function validated(Request $request, ?string $ignoreId): array
    {
        $request->merge([
            'is_active'         => $request->boolean('is_active'),
            'show_start_button' => $request->boolean('show_start_button'),
            'widget_auth_type'  => $request->input('widget_auth_type', 'register'),
        ]);

        return $request->validate([

            // UNIQUE NAME VALIDATION
            // store:  unique across the whole table
            // update: unique but ignore the row being edited
            'name' => [
                'required',
                'string',
                'max:255',
                $ignoreId
                    ? Rule::unique('face_widgets', 'name')->ignore($ignoreId)
                    : Rule::unique('face_widgets', 'name'),
            ],

            'mode'              => ['required', 'in:floating,embedded'],
            'widget_auth_type'  => ['required', 'in:register,login'],
            'position'          => ['required', 'in:top-right,top-left,top-center,bottom-center,bottom-right,bottom-left'],
            'theme_color'       => ['required', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'allowed_domains'   => ['required', 'string'],
            'allowed_pages'     => ['nullable', 'string'],
            'api_limit'         => ['required', 'integer', 'min:1'],
            'is_active'         => ['required', 'boolean'],
            'welcome_title'     => ['required', 'string', 'max:255'],
            'welcome_message'   => ['required', 'string'],
            'button_text'       => ['required', 'string', 'max:255'],
            'button_color'      => ['required', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'show_start_button' => ['required', 'boolean'], // NEW: widget button visibility toggle

        ], [
            'name.unique'          => 'A widget with this name already exists. Please choose a different name.',
            'theme_color.regex'    => 'Color must be a valid hex code like #66b0ff.',
            'button_color.regex'   => 'Button color must be a valid hex code like #2563eb.',
        ]);
    }
}