<?php
// Shared form fields for add/edit valuation pages.
// Expects $errors, $values, $NAMEN_OPTIONS, $PODLAGA_OPTIONS, $PREMISA_OPTIONS to be set.

function fieldError(array $errors, string $key): string {
    if (!isset($errors[$key])) return '';
    return '<span class="error-msg">' . htmlspecialchars($errors[$key], ENT_QUOTES, 'UTF-8') . '</span>';
}

function selectOptions(array $options, string $selected): string {
    $html = '<option value="">— Izberite —</option>';
    foreach ($options as $opt) {
        $sel   = ($opt === $selected) ? ' selected' : '';
        $label = htmlspecialchars($opt, ENT_QUOTES, 'UTF-8');
        $html .= "<option value=\"{$label}\"{$sel}>{$label}</option>";
    }
    return $html;
}
?>

<div class="form-group <?= isset($errors['naziv_narocnika']) ? 'has-error' : '' ?>">
    <label for="naziv_narocnika">Naziv naročnika</label>
    <input type="text" id="naziv_narocnika" name="naziv_narocnika"
           value="<?= htmlspecialchars($values['naziv_narocnika'], ENT_QUOTES, 'UTF-8') ?>" required>
    <?= fieldError($errors, 'naziv_narocnika') ?>
</div>

<div class="form-group <?= isset($errors['naslov_narocnika']) ? 'has-error' : '' ?>">
    <label for="naslov_narocnika">Naslov naročnika</label>
    <input type="text" id="naslov_narocnika" name="naslov_narocnika"
           value="<?= htmlspecialchars($values['naslov_narocnika'], ENT_QUOTES, 'UTF-8') ?>" required>
    <?= fieldError($errors, 'naslov_narocnika') ?>
</div>

<div class="form-group <?= isset($errors['namen_cenitve']) ? 'has-error' : '' ?>">
    <label for="namen_cenitve">Namen cenitve</label>
    <select id="namen_cenitve" name="namen_cenitve" required>
        <?= selectOptions($NAMEN_OPTIONS, $values['namen_cenitve']) ?>
    </select>
    <?= fieldError($errors, 'namen_cenitve') ?>
</div>

<div class="form-group <?= isset($errors['podlaga_vrednosti']) ? 'has-error' : '' ?>">
    <label for="podlaga_vrednosti">Podlaga vrednosti</label>
    <select id="podlaga_vrednosti" name="podlaga_vrednosti" required>
        <?= selectOptions($PODLAGA_OPTIONS, $values['podlaga_vrednosti']) ?>
    </select>
    <?= fieldError($errors, 'podlaga_vrednosti') ?>
</div>

<div class="form-group <?= isset($errors['premisa_vrednosti']) ? 'has-error' : '' ?>">
    <label for="premisa_vrednosti">Premisa vrednosti</label>
    <select id="premisa_vrednosti" name="premisa_vrednosti" required>
        <?= selectOptions($PREMISA_OPTIONS, $values['premisa_vrednosti']) ?>
    </select>
    <?= fieldError($errors, 'premisa_vrednosti') ?>
</div>

<div class="form-group <?= isset($errors['prvi_ogled']) ? 'has-error' : '' ?>">
    <label for="prvi_ogled">Datum in čas prvega ogleda</label>
    <input type="datetime-local" id="prvi_ogled" name="prvi_ogled"
           value="<?= htmlspecialchars(
               $values['prvi_ogled'] !== ''
                   ? date('Y-m-d\TH:i', strtotime($values['prvi_ogled']))
                   : '',
               ENT_QUOTES, 'UTF-8'
           ) ?>" required>
    <?= fieldError($errors, 'prvi_ogled') ?>
</div>
