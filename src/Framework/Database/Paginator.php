<?php

declare(strict_types=1);

namespace Veldora\Framework\Database;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

/**
 * @template T
 * @implements IteratorAggregate<int, T>
 */
class Paginator implements IteratorAggregate, Countable, JsonSerializable
{
    /**
     * @param array<T> $items
     */
    public function __construct(
        protected array $items,
        protected int $total,
        protected int $perPage = 15,
        protected int $currentPage = 1,
        protected string $path = '/'
    ) {
        $this->currentPage = max(1, $this->currentPage);
        $this->perPage = max(1, $this->perPage);
    }

    /**
     * Get the items being paginated.
     *
     * @return array<T>
     */
    public function items(): array
    {
        return $this->items;
    }

    /**
     * Get the total number of items before slicing.
     */
    public function total(): int
    {
        return $this->total;
    }

    /**
     * Get the number of items per page.
     */
    public function perPage(): int
    {
        return $this->perPage;
    }

    /**
     * Get the current page.
     */
    public function currentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * Get the last page number.
     */
    public function lastPage(): int
    {
        return max(1, (int) ceil($this->total / $this->perPage));
    }

    /**
     * Determine if there are more pages.
     */
    public function hasMorePages(): bool
    {
        return $this->currentPage() < $this->lastPage();
    }

    /**
     * Determine if there are enough items to split into multiple pages.
     */
    public function hasPages(): bool
    {
        return $this->currentPage() !== 1 || $this->hasMorePages();
    }

    /**
     * Get the URL for a given page number.
     */
    public function url(int $page): string
    {
        if ($page <= 0) {
            $page = 1;
        }

        $query = $_GET ?? [];
        $query['page'] = $page;

        $queryString = http_build_query($query);
        $base = parse_url($this->path, PHP_URL_PATH) ?: '/';

        return $base . ($queryString ? '?' . $queryString : '');
    }

    /**
     * Get the URL for the next page.
     */
    public function nextPageUrl(): ?string
    {
        if ($this->hasMorePages()) {
            return $this->url($this->currentPage() + 1);
        }

        return null;
    }

    /**
     * Get the URL for the previous page.
     */
    public function previousPageUrl(): ?string
    {
        if ($this->currentPage() > 1) {
            return $this->url($this->currentPage() - 1);
        }

        return null;
    }

    /**
     * Render the pagination links in accessible, modern HTML matching Veldora UI.
     */
    public function links(): string
    {
        if (!$this->hasPages()) {
            return '';
        }

        $current = $this->currentPage();
        $last = $this->lastPage();

        $html = '<nav class="v-pagination" aria-label="Pagination Navigation" style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; font-family: inherit; margin: 1.5rem 0;">';

        // Showing results summary
        $firstItem = (($current - 1) * $this->perPage) + 1;
        $lastItem = min($current * $this->perPage, $this->total);
        $html .= "<div class=\"v-pagination-info\" style=\"font-size: 0.875rem; color: #64748b;\">Showing <span style=\"font-weight: 600; color: #0f172a;\">{$firstItem}</span> to <span style=\"font-weight: 600; color: #0f172a;\">{$lastItem}</span> of <span style=\"font-weight: 600; color: #0f172a;\">{$this->total}</span> results</div>";

        $html .= '<ul class=\"v-pagination-list\" style=\"display: flex; list-style: none; padding: 0; margin: 0; gap: 0.25rem;\">';

        // Previous button
        if ($current > 1) {
            $prevUrl = $this->previousPageUrl();
            $html .= "<li><a href=\"{$prevUrl}\" class=\"v-page-btn\" style=\"display: inline-flex; align-items: center; justify-content: center; min-width: 2rem; height: 2rem; padding: 0 0.5rem; font-size: 0.875rem; border: 1px solid #e2e8f0; border-radius: 0.375rem; background: #ffffff; color: #334155; text-decoration: none; transition: all 0.2s;\">&laquo; Prev</a></li>";
        } else {
            $html .= "<li><span class=\"v-page-btn disabled\" style=\"display: inline-flex; align-items: center; justify-content: center; min-width: 2rem; height: 2rem; padding: 0 0.5rem; font-size: 0.875rem; border: 1px solid #f1f5f9; border-radius: 0.375rem; background: #f8fafc; color: #cbd5e1; cursor: not-allowed;\">&laquo; Prev</span></li>";
        }

        // Page numbers window (sliding window of 5 pages)
        $start = max(1, $current - 2);
        $end = min($last, $current + 2);

        if ($start > 1) {
            $html .= "<li><a href=\"{$this->url(1)}\" class=\"v-page-btn\" style=\"display: inline-flex; align-items: center; justify-content: center; min-width: 2rem; height: 2rem; padding: 0 0.5rem; font-size: 0.875rem; border: 1px solid #e2e8f0; border-radius: 0.375rem; background: #ffffff; color: #334155; text-decoration: none;\">1</a></li>";
            if ($start > 2) {
                $html .= "<li><span style=\"display: inline-flex; align-items: center; justify-content: center; min-width: 2rem; height: 2rem; color: #94a3b8;\">...</span></li>";
            }
        }

        for ($p = $start; $p <= $end; $p++) {
            if ($p === $current) {
                $html .= "<li><span class=\"v-page-btn active\" aria-current=\"page\" style=\"display: inline-flex; align-items: center; justify-content: center; min-width: 2rem; height: 2rem; padding: 0 0.5rem; font-size: 0.875rem; font-weight: 600; border: 1px solid #7c3aed; border-radius: 0.375rem; background: #7c3aed; color: #ffffff;\">{$p}</span></li>";
            } else {
                $html .= "<li><a href=\"{$this->url($p)}\" class=\"v-page-btn\" style=\"display: inline-flex; align-items: center; justify-content: center; min-width: 2rem; height: 2rem; padding: 0 0.5rem; font-size: 0.875rem; border: 1px solid #e2e8f0; border-radius: 0.375rem; background: #ffffff; color: #334155; text-decoration: none;\">{$p}</a></li>";
            }
        }

        if ($end < $last) {
            if ($end < $last - 1) {
                $html .= "<li><span style=\"display: inline-flex; align-items: center; justify-content: center; min-width: 2rem; height: 2rem; color: #94a3b8;\">...</span></li>";
            }
            $html .= "<li><a href=\"{$this->url($last)}\" class=\"v-page-btn\" style=\"display: inline-flex; align-items: center; justify-content: center; min-width: 2rem; height: 2rem; padding: 0 0.5rem; font-size: 0.875rem; border: 1px solid #e2e8f0; border-radius: 0.375rem; background: #ffffff; color: #334155; text-decoration: none;\">{$last}</a></li>";
        }

        // Next button
        if ($this->hasMorePages()) {
            $nextUrl = $this->nextPageUrl();
            $html .= "<li><a href=\"{$nextUrl}\" class=\"v-page-btn\" style=\"display: inline-flex; align-items: center; justify-content: center; min-width: 2rem; height: 2rem; padding: 0 0.5rem; font-size: 0.875rem; border: 1px solid #e2e8f0; border-radius: 0.375rem; background: #ffffff; color: #334155; text-decoration: none; transition: all 0.2s;\">Next &raquo;</a></li>";
        } else {
            $html .= "<li><span class=\"v-page-btn disabled\" style=\"display: inline-flex; align-items: center; justify-content: center; min-width: 2rem; height: 2rem; padding: 0 0.5rem; font-size: 0.875rem; border: 1px solid #f1f5f9; border-radius: 0.375rem; background: #f8fafc; color: #cbd5e1; cursor: not-allowed;\">Next &raquo;</span></li>";
        }

        $html .= '</ul></nav>';

        return $html;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function toArray(): array
    {
        return [
            'current_page' => $this->currentPage(),
            'data' => $this->items,
            'first_page_url' => $this->url(1),
            'from' => (($this->currentPage - 1) * $this->perPage) + 1,
            'last_page' => $this->lastPage(),
            'last_page_url' => $this->url($this->lastPage()),
            'next_page_url' => $this->nextPageUrl(),
            'path' => $this->path,
            'per_page' => $this->perPage(),
            'prev_page_url' => $this->previousPageUrl(),
            'to' => min($this->currentPage * $this->perPage, $this->total),
            'total' => $this->total(),
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
