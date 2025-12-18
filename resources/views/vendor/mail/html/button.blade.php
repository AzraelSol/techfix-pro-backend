@props([
    'url',
    'color' => 'primary',
    'align' => 'center',
])
<table class="action" align="{{ $align }}" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="{{ $align }}">
<table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="{{ $align }}">
<table border="0" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td>
<a href="{{ $url }}" class="button button-{{ $color }}" target="_blank" rel="noopener" style="
    display: inline-block;
    padding: 14px 32px;
    font-size: 15px;
    font-weight: 600;
    color: #ffffff !important;
    text-decoration: none;
    background: linear-gradient(135deg, #3b82f6 0%, #8a2be2 100%);
    border-radius: 12px;
    box-shadow: 0 4px 14px 0 rgba(59, 130, 246, 0.4);
    transition: all 0.2s ease;
">{{ $slot }}</a>
</td>
</tr>
</table>
</td>
</tr>
</table>
</td>
</tr>
</table>
