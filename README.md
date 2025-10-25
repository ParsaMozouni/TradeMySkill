# TradeMySkill – Local Skill Exchange Platform

A peer-to-peer learning platform that helps **local community members connect and exchange skills** instead of paying for traditional lessons.

Example use cases:
- A guitarist teaches a programmer code
- A photographer teaches Farsi in exchange for cooking lessons
- A newcomer in Regina finds a local native-English mentor

---

## 🌟 Features

### ✅ User Onboarding (3-step wizard)
- Step 1: Account info (name, email, password)
- Step 2: **Live map picker** (drag/drop or “Use my location” button)
- Step 3: **Skill selection with emoji icons**, smart search & category tree

### ✅ Listing System
- Users can **offer skills & request what they want to learn**
- Dynamic filters:
  - Search by skill / description / user name
  - Category tree selector
  - “Only show people who want what *I* can teach”
- Modal popup shows **user avatar, profile info, and approximate location map**

### ✅ Real-time Chat (Messenger UX)
- Chat button on listing
- Separate chat inbox page
- Messenger-style 2-column layout (You = right / Other user = left)
- Seen / unread system
- Live auto-refresh

### ✅ Profile & Skills Management
- Users can **edit their skills anytime** (same UX as signup step 3)

---

## 🗺 Built for Local Communities
The platform is optimized for **Regina, Saskatchewan** (but supports any city).
Location sharing is **approximate only** — no exact address is ever exposed.
This empowers users to meet/mentor/trade locally while protecting privacy.

---

## 🛠 Tech Stack

| Layer | Tech |
|--------|------|
| Backend | Laravel 10 + Livewire 3 |
| Frontend | TailwindCSS + Flux UI Components |
| Realtime | Laravel Echo (optional Pusher / WebSockets planned) |
| DB | MySQL (Prod) / SQLite (Dev) |
| Map | Leaflet + OpenStreetMap |
| Auth | Laravel Fortify |

---

## ⚙️ Setup (Dev)

```bash
git clone <repo-url>
cd urhacks

cp .env.example .env
# configure DB (MySQL or SQLite)

composer install
npm install && npm run dev

php artisan migrate --seed
php artisan serve
