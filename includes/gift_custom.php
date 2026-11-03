<?php

function gift_designs(): array
{
    return [
        'classic' => 'Classic & Elegant',
        'pastel' => 'Cute & Pastel',
        'luxury' => 'Luxury & Gold',
        'trendy' => 'Trendy & Colourful',
    ];
}

function gift_colors(): array
{
    return ['Ivory', 'Sage', 'Blush', 'Gold', 'Black', 'Lilac', 'Coral', 'Cream'];
}

function gift_custom_blank(): array
{
    return [
        'design' => 'classic',
        'color' => 'Ivory',
        'custom_name' => '',
        'message' => '',
        'note' => '',
    ];
}

function gift_clean_text(string $value, int $max): string
{
    $value = trim(preg_replace('/\s+/u', ' ', str_replace(['<', '>'], '', $value)) ?? '');
    return function_exists('mb_substr') ? mb_substr($value, 0, $max) : substr($value, 0, $max);
}

function gift_custom_from_post(): array
{
    $designs = gift_designs();
    $design = post('design', 20);
    if (!isset($designs[$design])) {
        $design = 'classic';
    }
    $color = post('color', 20);
    if (!in_array($color, gift_colors(), true)) {
        $color = 'Ivory';
    }
    return [
        'design' => $design,
        'color' => $color,
        'custom_name' => gift_clean_text(post('custom_name', 60), 60),
        'message' => gift_clean_text(post('gift_message', 180), 180),
        'note' => gift_clean_text(post('custom_note', 180), 180),
    ];
}

function gift_line_token(array $custom): string
{
    $custom = array_merge(gift_custom_blank(), $custom);
    return substr(hash('sha256', implode('|', [
        $custom['design'],
        $custom['color'],
        $custom['custom_name'],
        $custom['message'],
        $custom['note'],
    ])), 0, 16);
}

function gift_design_label(string $design): string
{
    return gift_designs()[$design] ?? '';
}

function gift_custom_summary(array $row): string
{
    $parts = [];
    $design = gift_design_label((string) ($row['design'] ?? ''));
    if ($design !== '') {
        $parts[] = $design;
    }
    $color = trim((string) ($row['color_name'] ?? ($row['color'] ?? '')));
    if ($color !== '') {
        $parts[] = $color;
    }
    $name = trim((string) ($row['custom_name'] ?? ''));
    if ($name !== '') {
        $parts[] = 'Name: ' . $name;
    }
    $message = trim((string) ($row['gift_message'] ?? ($row['message'] ?? '')));
    if ($message !== '') {
        $parts[] = 'Message: ' . $message;
    }
    $note = trim((string) ($row['custom_note'] ?? ($row['note'] ?? '')));
    if ($note !== '') {
        $parts[] = $note;
    }
    return implode(' · ', $parts);
}

function gift_design_photo(string $prefix, string $design): ?string
{
    $file = preg_replace('/[^0-9a-z-]/', '', $prefix . '-' . $design) . '.jpg';
    $path = ROOT_PATH . '/assets/images/designs/' . $file;
    if (!is_file($path)) {
        return null;
    }
    return base_url('assets/images/designs/' . $file);
}

function gift_custom_fields(array $selected, string $prefix, ?string $productPhoto): void
{
    $selected = array_merge(gift_custom_blank(), $selected);
    ?>
    <fieldset class="gift-custom" id="customise">
        <legend>Choose a design</legend>
        <div class="design-row">
            <?php foreach (gift_designs() as $key => $label): ?>
                <?php $photo = gift_design_photo($prefix, $key) ?: $productPhoto; ?>
                <label class="design-pick">
                    <input type="radio" name="design" value="<?= e($key) ?>" data-design-photo="<?= e((string) $photo) ?>" <?= $selected['design'] === $key ? 'checked' : '' ?>>
                    <?php if ($photo): ?><img src="<?= e($photo) ?>" alt="<?= e($label) ?>"><?php endif; ?>
                    <span><?= e($label) ?></span>
                </label>
            <?php endforeach; ?>
        </div>
        <div class="row g-2 mt-2">
            <div class="col-md-6">
                <label class="form-label" for="gift-color">Colour</label>
                <select class="form-select" id="gift-color" name="color">
                    <?php foreach (gift_colors() as $color): ?>
                        <option value="<?= e($color) ?>" <?= $selected['color'] === $color ? 'selected' : '' ?>><?= e($color) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="gift-name">Name on the gift</label>
                <input class="form-control" id="gift-name" name="custom_name" maxlength="60" value="<?= e($selected['custom_name']) ?>" placeholder="A letter, a name, or leave blank">
            </div>
            <div class="col-12">
                <label class="form-label" for="gift-message">Message</label>
                <input class="form-control" id="gift-message" name="gift_message" maxlength="180" value="<?= e($selected['message']) ?>" placeholder="A short note to print or write with the gift">
            </div>
            <div class="col-12">
                <label class="form-label" for="gift-note">Other details</label>
                <input class="form-control" id="gift-note" name="custom_note" maxlength="180" value="<?= e($selected['note']) ?>" placeholder="Colours to avoid, age, or how you want it finished">
            </div>
        </div>
    </fieldset>
    <?php
}
