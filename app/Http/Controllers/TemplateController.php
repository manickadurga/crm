<?php
namespace App\Http\Controllers;

use App\Models\Template;
use App\Mail\TemplateMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    public function store(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'template_name' => 'required|string|max:255',
            'body' => 'required|array',  // Expect body to be an array
            // 'body.*.type' => 'required|string',  // Each body entry must have a type (e.g., text, image, button)
            // 'body.*.content' => 'nullable|string',  // Content for text fields
            // 'body.*.url' => 'nullable|url',  // URL for images or buttons
            // 'body.*.label' => 'nullable|string',  // Label for buttons
            // 'body.*.link' => 'nullable|url',  // Link for buttons
        ]);

        // Store the validated data
        $template = Template::create([
            'template_name' => $validatedData['template_name'],
            'body' => $validatedData['body'],  // Store body as a JSON array
        ]);

        // Return a response
        return response()->json(['message' => 'Template created successfully!', 'template' => $template], 201);
    }

    // Optional: List all templates
    public function index()
    {
        $templates = Template::all();
        return response()->json($templates);
    }

    // Optional: Show a single template
    public function show($id)
    {
        $template = Template::findOrFail($id);
        return response()->json($template);
    }

    // Optional: Update a template
    public function update(Request $request, $id)
    {
        $template = Template::findOrFail($id);

        $validatedData = $request->validate([
            'template_name' => 'required|string|max:255',
            'body' => 'required|array',
            // 'body.*.type' => 'required|string',
            // 'body.*.content' => 'nullable|string',
            // 'body.*.url' => 'nullable|url',
            // 'body.*.label' => 'nullable|string',
            // 'body.*.link' => 'nullable|url',
        ]);

        $template->update([
            'template_name' => $validatedData['template_name'],
            'body' => $validatedData['body'],
        ]);

        return response()->json(['message' => 'Template updated successfully!', 'template' => $template]);
    }

    // Optional: Delete a template
    public function destroy($id)
    {
        $template = Template::findOrFail($id);
        $template->delete();

        return response()->json(['message' => 'Template deleted successfully!']);
    }
    
    public function sendTemplateEmail(Request $request, $templateId)
    {
        // Fetch the template by ID
        $template = Template::findOrFail($templateId);

        // Replace placeholders (for example, here it assumes {{contact.first_name}} is the only placeholder)
        $recipient = [
            'contact.first_name' => $request->input('first_name', 'User') // Default to 'User' if not provided
        ];

        $body = array_map(function($component) use ($recipient) {
            if (isset($component['attributes']['content'])) {
                $component['attributes']['content'] = str_replace(
                    '{{contact.first_name}}', 
                    $recipient['contact.first_name'], 
                    $component['attributes']['content']
                );
            }
            return $component;
        }, $template->body);

        // Send the email using the TemplateMail Mailable class
        Mail::to($request->input('email'))
            ->send(new TemplateMail($template->template_name, $body));

        return response()->json(['message' => 'Template email sent successfully!']);
    }
}
