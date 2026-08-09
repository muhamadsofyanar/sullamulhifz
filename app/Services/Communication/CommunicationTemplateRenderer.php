<?php

namespace App\Services\Communication;

use App\Models\CommunicationTemplate;

class CommunicationTemplateRenderer
{
    /** @param array<string,scalar|null> $variables
     *  @return array{subject:?string,content:string}
     */
    public function render(CommunicationTemplate $template, array $variables): array
    {
        return [
            'subject' => $template->subject ? $this->replace($template->subject, $variables, false) : null,
            'content' => $this->replace($template->content, $variables, $template->channel === 'email'),
        ];
    }

    /** @param array<string,scalar|null> $variables */
    private function replace(string $template, array $variables, bool $escapeHtml): string
    {
        return (string) preg_replace_callback(
            '/\{\{\s*([a-z0-9_]+)\s*\}\}/i',
            static function (array $match) use ($variables, $escapeHtml): string {
                $value = (string) ($variables[$match[1]] ?? '');

                return $escapeHtml ? e($value) : $value;
            },
            $template,
        );
    }
}
