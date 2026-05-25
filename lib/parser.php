<?php
/**
 * Transcript TXT parser for Tw Podcast Transcript plugin
 *
 * Parses plain text transcripts into structured segments for Kirby blocks.
 * Supports timestamp normalization and speaker extraction.
 *
 * @author Technikwürze
 * @license MIT
 */

namespace Tw\Transcript;

class Parser
{
    /**
     * Parse a plain text transcript into an array of segments.
     *
     * Supports multiline format:
     * Speaker
     * Timestamp (MM:SS or HH:MM:SS)
     * Text (can be multiple lines)
     * [blank line]
     *
     * @param string $txt The raw transcript text
     * @return array<int, array{speaker: string, timestamp: string, text: string}>
     */
    public static function parse(string $txt): array
    {
        $segments = [];
        $lines = preg_split('/\r?\n/', trim($txt));
        
        $i = 0;
        while ($i < count($lines)) {
            $speaker = trim($lines[$i] ?? '');
            $i++;
            
            // Skip empty lines
            if ($speaker === '') {
                $i++;
                continue;
            }
            
            // Get timestamp (next line)
            $timestamp = trim($lines[$i] ?? '');
            $i++;
            
            // Validate timestamp format (MM:SS or HH:MM:SS)
            if (!preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', $timestamp)) {
                // If next line is not a valid timestamp, treat it as text continuation
                $textLines = [$timestamp];
                $timestamp = '';
                
                // Collect remaining text until blank line
                while ($i < count($lines)) {
                    $line = trim($lines[$i] ?? '');
                    if ($line === '') {
                        $i++;
                        break;
                    }
                    $textLines[] = $line;
                    $i++;
                }
                
                $text = implode("\n", $textLines);
            } else {
                // Collect text lines until blank line or EOF
                $textLines = [];
                while ($i < count($lines)) {
                    $line = trim($lines[$i] ?? '');
                    if ($line === '') {
                        $i++;
                        break;
                    }
                    $textLines[] = $line;
                    $i++;
                }
                
                $text = implode("\n", $textLines);
            }
            
            // Only add if we have speaker and text
            if ($speaker !== '' && $text !== '') {
                $segments[] = [
                    'speaker' => $speaker,
                    'timestamp' => $timestamp,
                    'text' => $text,
                ];
            }
        }
        
        return $segments;
    }
}
