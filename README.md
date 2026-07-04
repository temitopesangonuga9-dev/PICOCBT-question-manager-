# PICOCBT Question Manager

A companion tool for **PICOCBT** (Progress Intellectual College Okeigbo's CBT system).
Teachers create questions manually, generate them with AI, or convert lesson notes
into MCQs. Admins review submissions and export approved questions as CSV for
import into your main PICOCBT question bank.

## Features

- **Teacher Portal**: register → wait for admin approval → login
  - Create questions manually (MCQ or theory)
  - Generate questions with AI from a subject + topic
  - Upload a lesson note (.txt, .docx, .pdf) or paste text → AI converts it into MCQs/theory questions
  - Track status of all submitted questions (pending / approved / rejected)
- **Admin Portal**
  - Approve/suspend teacher accounts
  - Review every submitted question, approve or reject
  - Export approved questions as CSV, filterable by subject/class/type
  - Export history log

## 1. Installation (XAMPP / localhost)

1. Copy the whole `picocbt-qm` folder into your `htdocs` directory, e.g.
   `C:\xampp\htdocs\picocbt-qm`.
2. Open `config/db.php` and set `DB_NAME` to your existing PICOCBT database
   name (the same one your main CBT system uses), plus your MySQL username/password.
3. Open `config/openai.php` and paste your OpenAI API key into `OPENAI_API_KEY`.
   Get one at https://platform.openai.com/api-keys — you'll need a small amount
   of credit on the account; `gpt-4o-mini` (the default model used here) is cheap.
4. In your browser, visit:
   `http://localhost/picocbt-qm/sql/migration.php`
   This safely sets up the extra tables needed (`users`, `lesson_uploads`,
   `csv_exports`) and adds a few tracking columns to your **existing**
   `question_bank` table (`created_by`, `status`, `source`, `reviewed_by`,
   `reviewed_at`) — it will NOT touch or duplicate your existing questions.
   It also creates a default admin account. **Note the email/password it
   prints and change the password after your first login.**
5. Visit `http://localhost/picocbt-qm/` and you're ready to go.

## 2. How teachers use it

1. Teacher registers at `teacher/register.php` (selects their subject).
2. Admin approves the account under **Manage Teachers**.
3. Teacher logs in and can:
   - **Create Question** — write an MCQ/theory question by hand.
   - **Generate with AI** — enter a subject + topic + difficulty + count,
     click Generate, review/edit the AI's draft questions, then submit.
   - **Convert Lesson Note** — upload a `.docx`/`.pdf`/`.txt` file (or paste
     text), extract it, generate questions from that specific content,
     review/edit, then submit.
4. Every submission lands in the shared `question_bank` table with
   `status = 'pending'` until an admin reviews it.

## 3. How admins use it

1. Log in at `admin/login.php` with the seeded admin account (or one you create).
2. **Review Questions** — see every pending question (with options/answers
   shown inline), Approve or Reject each one.
3. **Export CSV** — filter by subject/class/type and download a CSV of every
   *approved* question, ready to feed into PICOCBT.

## 4. CSV format

The exported CSV has these columns:

```
subject, class_level, question_type, question_text,
option_a, option_b, option_c, option_d, correct_option,
theory_answer, difficulty
```

- `question_type` is `mcq` or `theory`.
- For MCQs, `correct_option` is the letter (`a`/`b`/`c`/`d`) of the right answer.
- For theory questions, the option columns are blank and `theory_answer`
  holds the model answer.

**Note:** I built this format based on typical question-bank CSV conventions.
If your `admin/question_bank.php` importer in PICOCBT expects different
column names or order, tell me the exact headers it expects (or share that
import script) and I'll adjust `includes/functions.php` → `streamQuestionsAsCsv()`
to match exactly.

## 5. PDF lesson note support

`.docx` and `.txt` lesson notes work out of the box. `.pdf` extraction needs
the `pdftotext` command-line tool (part of Poppler/Xpdf) installed on the
server running PHP. On Windows/XAMPP, the easiest options are:
- Install [Poppler for Windows](https://github.com/oschwartz10612/poppler-windows/releases)
  and add its `bin` folder to your system PATH, or
- Just ask teachers to paste the lesson note text directly instead of
  uploading a PDF (fully supported, no extra install needed).

## 6. Folder structure

```
picocbt-qm/
├── config/          - DB & OpenAI configuration
├── includes/         - auth, helpers, file parsing, header/footer
├── sql/migration.php - one-time safe DB setup script
├── teacher/          - teacher-facing pages
├── admin/            - admin-facing pages
├── assets/style.css   - shared styling
└── uploads/           - created automatically for lesson note uploads
```

## 8. Deploying online via GitHub + Railway (free to start)

This is the quickest way to get a real public link without buying hosting yet.

1. **Create the GitHub repo**
   - Go to https://github.com/new, create a repo (e.g. `picocbt-question-manager`), keep it **Private** since it'll contain your setup.
   - Upload everything in this folder to that repo (drag-and-drop on the GitHub web UI works fine, or use `git push` if you're comfortable with git).

2. **Sign up at https://railway.app** (free trial credit, then pay-as-you-go - a small app like this costs only a few dollars a month).

3. **New Project → Deploy from GitHub repo** → pick your `picocbt-question-manager` repo.
   Railway will detect the `Dockerfile` in this project and build/deploy it automatically.

4. **Add a MySQL database**: in the same Railway project, click **+ New → Database → Add MySQL**.
   Railway creates it and gives you connection variables (`MYSQLHOST`, `MYSQLPORT`, `MYSQLUSER`, `MYSQLPASSWORD`, `MYSQLDATABASE`).

5. **Set environment variables** on your app service (Railway → your service → Variables tab):
   ```
   DB_HOST      = <MYSQLHOST value>
   DB_PORT      = <MYSQLPORT value>
   DB_NAME      = <MYSQLDATABASE value>
   DB_USER      = <MYSQLUSER value>
   DB_PASS      = <MYSQLPASSWORD value>
   OPENAI_API_KEY = <your real OpenAI key>
   ```
   (`config/db.php` and `config/openai.php` already read these automatically -
   no code changes needed.)

6. **Generate a public domain**: Railway → your service → Settings → Networking → **Generate Domain**.
   You'll get a link like `picocbt-question-manager-production.up.railway.app`.
   You can later point your own domain/subdomain (e.g. `qm.myicbt.com.ng`) at it too.

7. **Run the migration once**: visit `https://<your-railway-domain>/sql/migration.php`
   in your browser to set up the tables (safe to run once). Note the seeded
   admin email/password it prints, then delete or block access to that file.

8. **You're live** — visit `https://<your-railway-domain>/` to use the Teacher/Admin portals.

**Important:** since this Railway MySQL database is a *new, separate* database
from whatever database your live PICOCBT/myicbt.com.ng site uses, questions
approved here won't automatically appear in your live CBT until you either
(a) import the exported CSV into your live PICOCBT database manually, or
(b) point `DB_HOST`/`DB_NAME` etc. at your actual PICOCBT production database
instead of a fresh Railway one (only do this if that database allows secure
remote connections - most shared cPanel hosts restrict this for security).

## 9. Security notes

- Change the default admin password immediately after first login (there's
  no "change password" page yet — update it directly in the `users` table
  using `password_hash()`, or ask me to add a settings page).
- Delete or password-protect `sql/migration.php` after running it once.
- Keep `config/openai.php` out of any public git repo.
