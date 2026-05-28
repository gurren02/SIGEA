<div class="form-row">
    <label>Materia
        <select name="subject_id" required>
            <option value="">Seleccionar materia</option>
            <?php foreach ($subjects as $subject): ?>
                <option value="<?= (int) $subject['id'] ?>"><?= e($subject['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Unidad
        <select name="unit" required>
            <option value="">Seleccionar unidad</option>
            <?php foreach (Exam::units() as $unit): ?>
                <option value="<?= $unit ?>">UNIDAD <?= $unit ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Puntos
        <input type="number" min="1" step="0.5" name="score" value="1" required>
    </label>
</div>
