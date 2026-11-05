<?php

namespace App\Services;

use Dompdf\Canvas;
use Dompdf\Frame;

class PdfSignatureLayout
{
    public static function alignToBottom(Frame $frame, Canvas $canvas): void
    {
        $node = $frame->get_node();
        if (!$node instanceof \DOMElement || !$node->hasAttribute('data-pdf-signature')) {
            return;
        }

        // Normal flow first reserves space and moves the whole signature to a new
        // page if necessary. At render time, only move it down into unused space.
        // Leave at least one inch below the signature for a clear footer area.
        $bottomMargin = max(72.0, (float) $node->getAttribute('data-pdf-signature'));
        $targetY = $canvas->get_height() - $bottomMargin - $frame->get_margin_height();
        $offset = $targetY - $frame->get_position('y');
        if ($offset > 0) {
            $frame->move(0, $offset);
        }
    }
}
