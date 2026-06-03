<?php

class User
{
    public static function find(int $id): ?array
    {
        $stmt = Database::get()->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function findByEmail(string $email): ?array
    {
        $stmt = Database::get()->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function create(array $data): void
    {
        $stmt = Database::get()->prepare(
            'INSERT INTO users (name, email, password, role, phone, institutional_id) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['name'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['role'],
            $data['phone'] ?? null,
            $data['institutional_id'] ?? null,
        ]);
    }

    public static function updateProfile(int $id, array $data): void
    {
        $stmt = Database::get()->prepare(
            'UPDATE users SET name = ?, phone = ?, institutional_id = ? WHERE id = ?'
        );
        $stmt->execute([
            $data['name'],
            $data['phone'] ?? null,
            $data['institutional_id'] ?? null,
            $id,
        ]);
    }

    public static function updatePassword(int $id, string $password): void
    {
        $stmt = Database::get()->prepare('UPDATE users SET password = ? WHERE id = ?');
        $stmt->execute([password_hash($password, PASSWORD_DEFAULT), $id]);
    }

    public static function allByRole(string $role): array
    {
        $stmt = Database::get()->prepare('SELECT * FROM users WHERE role = ? ORDER BY name');
        $stmt->execute([$role]);
        return $stmt->fetchAll();
    }

    public static function countsByRole(): array
    {
        $rows = Database::get()->query('SELECT role, COUNT(*) total FROM users GROUP BY role')->fetchAll();
        return array_column($rows, 'total', 'role');
    }

    public static function teachersWithStudentCounts(): array
    {
        return Database::get()->query(
            "SELECT u.*, COUNT(ts.student_id) AS students_count
             FROM users u
             LEFT JOIN teacher_students ts ON ts.teacher_id = u.id
             WHERE u.role = 'teacher'
             GROUP BY u.id
             ORDER BY u.name"
        )->fetchAll();
    }

    public static function assignedStudentIds(int $teacherId): array
    {
        $stmt = Database::get()->prepare('SELECT student_id FROM teacher_students WHERE teacher_id = ?');
        $stmt->execute([$teacherId]);
        return array_map('intval', array_column($stmt->fetchAll(), 'student_id'));
    }

    public static function assignStudents(int $teacherId, array $studentIds): void
    {
        $db = Database::get();
        $db->beginTransaction();
        $db->prepare('DELETE FROM teacher_students WHERE teacher_id = ?')->execute([$teacherId]);
        $stmt = $db->prepare('INSERT INTO teacher_students (teacher_id, student_id) VALUES (?, ?)');
        foreach ($studentIds as $studentId) {
            $stmt->execute([$teacherId, (int) $studentId]);
        }
        $db->commit();
    }

    public static function studentsByTeacher(int $teacherId): array
    {
        $stmt = Database::get()->prepare(
            "SELECT u.*
             FROM users u
             INNER JOIN teacher_students ts ON ts.student_id = u.id
             WHERE ts.teacher_id = ?
             ORDER BY u.name"
        );
        $stmt->execute([$teacherId]);
        return $stmt->fetchAll();
    }

    public static function teacherForStudent(int $studentId): ?array
    {
        $stmt = Database::get()->prepare(
            "SELECT u.*
             FROM users u
             INNER JOIN teacher_students ts ON ts.teacher_id = u.id
             WHERE ts.student_id = ?
             ORDER BY ts.assigned_at DESC
             LIMIT 1"
        );
        $stmt->execute([$studentId]);
        $teacher = $stmt->fetch();
        return $teacher ?: null;
    }

    public static function assignedSubjectIds(int $teacherId): array
    {
        $stmt = Database::get()->prepare('SELECT subject_id FROM teacher_subjects WHERE teacher_id = ?');
        $stmt->execute([$teacherId]);
        return array_map('intval', array_column($stmt->fetchAll(), 'subject_id'));
    }

    public static function assignSubjects(int $teacherId, array $subjectIds): void
    {
        $db = Database::get();
        $db->beginTransaction();
        $db->prepare('DELETE FROM teacher_subjects WHERE teacher_id = ?')->execute([$teacherId]);
        $stmt = $db->prepare('INSERT INTO teacher_subjects (teacher_id, subject_id) VALUES (?, ?)');
        foreach ($subjectIds as $subjectId) {
            $stmt->execute([$teacherId, (int) $subjectId]);
        }
        $db->commit();
    }

    public static function subjectsByTeacher(int $teacherId): array
    {
        $stmt = Database::get()->prepare(
            "SELECT s.*
             FROM subjects s
             INNER JOIN teacher_subjects ts ON ts.subject_id = s.id
             WHERE ts.teacher_id = ?
             ORDER BY s.name"
        );
        $stmt->execute([$teacherId]);
        return $stmt->fetchAll();
    }

    public static function delete(int $id): void
    {
        $stmt = Database::get()->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$id]);
    }
}

