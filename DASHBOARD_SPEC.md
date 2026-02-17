# DASHBOARD_SPEC.md — Louer un Chauffeur Prestige (LCP)

Full-stack blueprint for the React frontend + Laravel API integration.

---

## 1. Architecture Overview

| Layer | Stack |
|-------|-------|
| Frontend | React 18 + TypeScript + Vite (port 8080) |
| UI | shadcn-ui + Tailwind CSS |
| State | React Context (auth) + @tanstack/react-query (server data) |
| HTTP | Axios instance (`src/lib/api.ts`) with Bearer token interceptor |
| Forms | react-hook-form + zod |
| Backend | Laravel 12 + Passport (Personal Access Tokens) |
| Database | MySQL / PostgreSQL |
| API Format | `{ success, data, message }` from BaseController |

### API Response Envelope

Every endpoint returns:

```json
{
  "success": true,
  "data": { ... },
  "message": "Message en français."
}
```

Errors return:

```json
{
  "success": false,
  "message": "Error description.",
  "data": { "field": ["validation error"] }
}
```

---

## 2. User Roles & Routing

| Role | UserType name | Frontend base path | Sidebar menus |
|------|--------------|-------------------|---------------|
| Client | `client` | `/mon-compte/*` | Client menu |
| Chauffeur | `driver` | `/mon-compte/*` | Chauffeur menu |
| Company | `company` | `/mon-compte/*` | Client menu |
| Admin | `admin` | `/admin/*` | Admin menu (TBD) |

### Role detection
`GET /api/auth/me` returns `user_type.name` which maps to frontend roles:
- `admin` → admin
- `driver` → chauffeur
- `company` → company
- `client` → client (default)

---

## 3. Authentication Flow

### 3.1 Registration
```
Frontend: POST /api/auth/register
Payload: { first_name, last_name, email, phone, password, password_confirmation, user_type? }
Response: { success, data: UserResource, message }

→ User receives 6-digit email verification code
→ Redirect to /connexion with "please verify email" message
```

### 3.2 Email Verification
```
POST /api/verification/verify     { email, code }
POST /api/verification/resend     { email }
POST /api/verification/status     { email }
```

### 3.3 Login
```
POST /api/auth/login
Payload: { email, password, remember? }
Response: { success, data: AuthTokenResource, message }

AuthTokenResource: { email, token_type, expires_in, token, refresh_token, client_type }

→ Store token in localStorage
→ Fetch GET /api/auth/me to get user profile
→ Redirect based on role
```

### 3.4 Token Refresh
```
POST /api/auth/refresh-token (auth:api + verified)
→ Revokes current token, creates new one
```

### 3.5 Password Reset
```
POST /api/password/request-reset    { email }          → sends 6-digit code
POST /api/password/verify-code      { email, code }    → returns verification_token
POST /api/password/resend-code      { email }          → resends code
POST /api/password/reset            { email, verification_token, password, password_confirmation }
```

### 3.6 Google OAuth
```
GET  /api/google/authorize   { client_id, redirect_uri, scope?, state? }
GET  /api/google/callback    (handled server-side, redirects to frontend)
POST /api/google/token       { code }   → returns AuthTokenResource
```

### 3.7 Logout
```
POST /api/auth/logout       → revokes current token
POST /api/auth/logout-all   → revokes all tokens
```

---

## 4. Dashboard Pages — Client

### 4.1 Profile (`/mon-compte/modifier/profil`) — CONNECTED
| Field | API |
|-------|-----|
| Read | `GET /api/auth/me` |
| Update | `PUT /api/auth/profile` → `{ first_name, last_name, email, phone }` |
| Avatar | `POST /api/users/{uuid}/avatar` → multipart |
| Password | `PUT /api/auth/change-password` → `{ current_password, password, password_confirmation }` |

### 4.2 Subscriptions (`/mon-compte/souscription`)
| Action | API | Status |
|--------|-----|--------|
| List plans | Static data in frontend | Built (UI only) |
| Subscribe | **MISSING** — needs `POST /api/subscriptions` | To build |
| Current sub | **MISSING** — needs `GET /api/subscriptions/current` | To build |
| Cancel | **MISSING** — needs `POST /api/subscriptions/cancel` | To build |

### 4.3 Simulations (`/mon-compte/mes-simulations`)
| Action | API | Status |
|--------|-----|--------|
| List | `GET /api/rides?type=quote` (filter quotes) | Map to API |
| Create | `POST /api/rides/quote` (public) | Map to API |
| Convert to ride | `POST /api/rides` | Map to API |

**API mapping**: The `quote` endpoint and `rides` CRUD handle this. Need to add `status=quote` filter support or use ride_quotes table.

### 4.4 Reservations (`/mon-compte/mes-reservations`)
| Action | API |
|--------|-----|
| List | `GET /api/rides?status=pending,confirmed` |
| View details | `GET /api/rides/{uuid}` |
| Cancel | `POST /api/rides/{uuid}/cancel` |

### 4.5 Upcoming Courses (`/mon-compte/mes-prochaines-courses`)
| Action | API |
|--------|-----|
| List | `GET /api/rides?status=confirmed&upcoming=true` |
| View details | `GET /api/rides/{uuid}` |

### 4.6 Course History (`/mon-compte/historique-des-courses`)
| Action | API |
|--------|-----|
| List | `GET /api/rides?status=completed` |
| View details | `GET /api/rides/{uuid}` |
| Leave review | `POST /api/reviews` → `{ ride_id, rating, comment }` |

### 4.7 Pending Payments (`/mon-compte/paiement-en-attente`)
| Action | API |
|--------|-----|
| List | `GET /api/payments?status=pending` |
| Pay | `POST /api/payments/intent` → `POST /api/payments/confirm` |
| History | `GET /api/payments/history` |

### 4.8 Pending PV (`/mon-compte/pv-en-attente`)
| Action | API | Status |
|--------|-----|--------|
| List violations | **Partial** — `Violation` model exists | Need `GET /api/violations` endpoint |
| View details | **MISSING** | Need `GET /api/violations/{id}` |
| Contest | **MISSING** | Need `POST /api/violations/{id}/contest` |

### 4.9 Billing Info (`/mon-compte/informations-de-facturation`)
| Action | API |
|--------|-----|
| List payment methods | `GET /api/payment-methods` |
| Add card | `POST /api/payment-methods` |
| Set default | `POST /api/payment-methods/{id}/set-default` |
| Remove | `DELETE /api/payment-methods/{id}` |

---

## 5. Dashboard Pages — Chauffeur

### 5.1 Upcoming Courses (`/mon-compte/mes--prochaines-courses`)
| Action | API |
|--------|-----|
| List assigned rides | `GET /api/rides?role=driver&status=confirmed` |
| View details | `GET /api/rides/{uuid}` |

### 5.2 Course History (`/mon-compte/historique--des-courses`)
| Action | API |
|--------|-----|
| List completed | `GET /api/rides?role=driver&status=completed` |

### 5.3 Start Course (`/mon-compte/demarrer-une-course`)
| Action | API |
|--------|-----|
| List ready rides | `GET /api/rides?role=driver&status=confirmed` |
| Start | `POST /api/rides/{uuid}/start` |

### 5.4 End Course (`/mon-compte/terminer-une-course`)
| Action | API |
|--------|-----|
| List in-progress | `GET /api/rides?role=driver&status=in_progress` |
| Complete | `POST /api/rides/{uuid}/complete` |

### 5.5 Pending Payments (`/mon-compte/paiement--en-attente`)
| Action | API | Status |
|--------|-----|--------|
| Driver payouts | **Partial** — `DriverPayout` model exists | Need `GET /api/driver-payouts` endpoint |

### 5.6 Pending PV (`/mon-compte/pv--en-attente`)
| Action | API | Status |
|--------|-----|--------|
| List violations | **MISSING** | Need `GET /api/violations?role=driver` |

### 5.7 Bank Info (`/mon-compte/information-bancaire`)
| Action | API | Status |
|--------|-----|--------|
| View/update bank details | **Partial** — driver_profile has fields | Use `PUT /api/drivers/{uuid}` with bank fields |

---

## 6. Admin Dashboard (To Build)

Base path: `/admin/*`

### 6.1 Dashboard Overview (`/admin/tableau-de-bord`)
| Widget | API |
|--------|-----|
| Stats cards | `GET /api/admin/statistics/dashboard` |
| Revenue chart | `GET /api/admin/statistics/revenue` |
| Ride stats | `GET /api/admin/statistics/rides` |
| Driver stats | `GET /api/admin/statistics/drivers` |

### 6.2 User Management (`/admin/utilisateurs`)
| Action | API |
|--------|-----|
| List users | `GET /api/admin/users` |
| View user | `GET /api/admin/users/{uuid}` |
| Edit user | `PUT /api/admin/users/{uuid}` |
| Activate | `POST /api/admin/users/{uuid}/activate` |
| Deactivate | `POST /api/admin/users/{uuid}/deactivate` |

### 6.3 Driver Verification (`/admin/verification-chauffeurs`)
| Action | API |
|--------|-----|
| Pending list | `GET /api/admin/driver-verification` |
| Approve | `POST /api/admin/driver-verification/{uuid}/approve` |
| Reject | `POST /api/admin/driver-verification/{uuid}/reject` |

### 6.4 Company Verification (`/admin/verification-societes`)
| Action | API |
|--------|-----|
| Pending list | `GET /api/admin/company-verification` |
| Approve | `POST /api/admin/company-verification/{uuid}/approve` |
| Reject | `POST /api/admin/company-verification/{uuid}/reject` |

### 6.5 Rides Management (`/admin/courses`)
| Action | API |
|--------|-----|
| List all rides | `GET /api/rides` (admin sees all) |
| View ride | `GET /api/rides/{uuid}` |
| Assign driver | `POST /api/rides/{uuid}/assign` |
| Cancel ride | `POST /api/rides/{uuid}/cancel` |

### 6.6 Pricing Rules (`/admin/tarification`)
| Action | API |
|--------|-----|
| List rules | `GET /api/admin/pricing-rules` |
| Create rule | `POST /api/admin/pricing-rules` |
| Update rule | `PUT /api/admin/pricing-rules/{id}` |
| Delete rule | `DELETE /api/admin/pricing-rules/{id}` |

### 6.7 Content Management (`/admin/contenu`)
| Section | List API | Create | Update |
|---------|----------|--------|--------|
| Pages | `GET /api/pages` | Need admin POST | Need admin PUT |
| News | `GET /api/news` | Need admin POST | Need admin PUT |
| Banners | `GET /api/banners` | Need admin POST | Need admin PUT |
| Partners | `GET /api/partners` | Need admin POST | Need admin PUT |
| FAQs | `GET /api/faqs` | Need admin POST | Need admin PUT |

**Note**: ContentManagementController already has store/update/destroy methods. Need admin-only routes added for CMS write operations.

### 6.8 Payments & Refunds (`/admin/paiements`)
| Action | API |
|--------|-----|
| List payments | `GET /api/payments` (admin sees all) |
| View payment | `GET /api/payments/{uuid}` |
| Issue refund | `POST /api/refunds` |
| List refunds | `GET /api/refunds` |

### 6.9 Reviews Management (`/admin/avis`)
| Action | API |
|--------|-----|
| List reviews | `GET /api/reviews` |
| View review | `GET /api/reviews/{id}` |

### 6.10 Audit Logs (`/admin/journal`)
| Action | API |
|--------|-----|
| List logs | `GET /api/admin/audit-logs` |
| View log | `GET /api/admin/audit-logs/{id}` |

### 6.11 App Settings (`/admin/parametres`)
| Action | API |
|--------|-----|
| Get settings | `GET /api/admin/settings` |
| Update settings | `PUT /api/admin/settings` |

---

## 7. Missing Backend Endpoints

These API endpoints do not exist yet and need to be created:

| Endpoint | Purpose | Priority |
|----------|---------|----------|
| `GET /api/violations` | List violations for user/driver | High |
| `GET /api/violations/{id}` | View violation details | High |
| `POST /api/violations/{id}/contest` | Contest a violation | Medium |
| `GET /api/driver-payouts` | List driver payouts | High |
| `GET /api/driver-payouts/{id}` | View payout details | Medium |
| `POST /api/subscriptions` | Subscribe to a plan | Medium |
| `GET /api/subscriptions/current` | Get current subscription | Medium |
| `POST /api/subscriptions/cancel` | Cancel subscription | Medium |
| Admin CMS write routes | CRUD for pages/news/banners/partners/faqs | Medium |
| `GET /api/rides/quote` (GET) | List user's ride quotes | Low |

---

## 8. TypeScript Types

### Core types (from API responses, defined in `src/lib/api.ts`)

```typescript
// Already defined:
ApiResponse<T>     // { success, data: T, message }
ApiUser            // Full user from UserResource
AuthTokenData      // Token response
UserType           // { id, name, display_name }

// Need to add:
interface Ride {
  id: string;                // uuid
  customer: ApiUser;
  driver?: ApiUser;
  trip_type: { id: number; name: string };
  status: 'pending' | 'confirmed' | 'in_progress' | 'completed' | 'cancelled';
  pickup_address: string;
  dropoff_address: string;
  scheduled_at: string;
  started_at?: string;
  completed_at?: string;
  estimated_price: number;
  final_price?: number;
  distance_km?: number;
  duration_minutes?: number;
  waypoints: RideWaypoint[];
  payment?: Payment;
  review?: Review;
  created_at: string;
}

interface Payment {
  id: string;
  amount: number;
  currency: string;
  status: 'pending' | 'completed' | 'failed' | 'refunded';
  payment_method: string;
  stripe_payment_intent_id?: string;
  paid_at?: string;
  created_at: string;
}

interface Review {
  id: number;
  rating: number;
  comment: string;
  response?: string;
  reviewer: ApiUser;
  reviewee: ApiUser;
  ride_id: string;
  created_at: string;
}

interface Vehicle {
  id: string;
  brand: { id: number; name: string };
  model: { id: number; name: string };
  year: number;
  color: string;
  license_plate: string;
  seats: number;
  is_active: boolean;
}

interface DriverProfile {
  id: string;
  user: ApiUser;
  license_number: string;
  license_type: { id: number; name: string };
  experience_years: number;
  is_available: boolean;
  is_verified: boolean;
  vehicle?: Vehicle;
}

interface Company {
  id: string;
  name: string;
  siret: string;
  address: string;
  manager: ApiUser;
  is_verified: boolean;
  driver_count: number;
}

interface Violation {
  id: number;
  ride_id: string;
  type: string;
  description: string;
  fine_amount: number;
  status: 'pending' | 'reviewed' | 'resolved' | 'contested';
  date: string;
}

interface Notification {
  id: string;
  type: string;
  title: string;
  body: string;
  read_at: string | null;
  created_at: string;
}
```

---

## 9. State Management Plan

### React Query keys convention

```typescript
// Rides
['rides']                        // list
['rides', uuid]                  // single
['rides', { status, role }]      // filtered list

// Payments
['payments']                     // list
['payments', uuid]               // single
['payments', 'history']          // payment history

// Reviews
['reviews']                      // list
['reviews', id]                  // single

// Notifications
['notifications']                // list
['notifications', { unread: true }]  // unread only

// Admin
['admin', 'statistics', 'dashboard']
['admin', 'users']
['admin', 'driver-verification']
['admin', 'audit-logs']
```

### Custom hooks pattern

```typescript
// Example: useRides hook
function useRides(filters?: { status?: string; role?: string }) {
  return useQuery({
    queryKey: ['rides', filters],
    queryFn: () => api.get('/rides', { params: filters }).then(r => r.data.data),
  });
}

// Example: mutation
function useCancelRide() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: (uuid: string) => api.post(`/rides/${uuid}/cancel`),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['rides'] }),
  });
}
```

---

## 10. Implementation Phases

### Phase 1 — Foundation (Current)
- [x] Axios instance with interceptors (`src/lib/api.ts`)
- [x] AuthContext with real API calls
- [x] Login page connected to `POST /api/auth/login`
- [x] Register page connected to `POST /api/auth/register`
- [x] ProtectedRoute with loading state + role checking
- [x] DashboardLayout with role-based sidebar menus
- [x] ProfilePage connected to `PUT /api/auth/profile`
- [x] CORS config updated for frontend dev server
- [ ] Email verification page (new page needed)
- [ ] Password reset flow pages (new pages needed)

### Phase 2 — Client Dashboard
- [ ] Create `src/hooks/useRides.ts` — react-query hooks for rides
- [ ] Connect SimulationsPage to `POST /api/rides/quote` + `GET /api/rides`
- [ ] Connect ReservationsPage to `GET /api/rides?status=pending,confirmed`
- [ ] Connect UpcomingCoursesPage to `GET /api/rides?status=confirmed&upcoming=true`
- [ ] Connect CourseHistoryPage to `GET /api/rides?status=completed`
- [ ] Connect PendingPaymentsPage to `GET /api/payments?status=pending`
- [ ] Connect BillingInfoPage to `GET/POST/DELETE /api/payment-methods`
- [ ] Build PendingPVPage (needs violation endpoint)
- [ ] Build SubscriptionPage (needs subscription endpoints)

### Phase 3 — Chauffeur Dashboard
- [ ] Connect chauffeur upcoming courses to `GET /api/rides?role=driver`
- [ ] Connect StartCoursePage to `POST /api/rides/{uuid}/start`
- [ ] Connect EndCoursePage to `POST /api/rides/{uuid}/complete`
- [ ] Build BankInfoPage connected to `PUT /api/drivers/{uuid}` (bank fields)
- [ ] Build driver payout view (needs endpoint)

### Phase 4 — Admin Dashboard
- [ ] Create admin route layout (`/admin/*`) with AdminLayout component
- [ ] Build admin sidebar navigation
- [ ] Dashboard overview with statistics cards + charts (recharts)
- [ ] User management table with search/filter/pagination
- [ ] Driver verification queue with approve/reject actions
- [ ] Company verification queue
- [ ] Rides management table
- [ ] Pricing rules CRUD
- [ ] Payments & refunds management
- [ ] Content management (pages, news, banners, partners, FAQs)
- [ ] Audit logs viewer
- [ ] App settings panel

### Phase 5 — Polish
- [ ] Google OAuth login button on login page
- [ ] Real-time notifications (WebSocket or polling)
- [ ] Stripe Elements integration for payment methods
- [ ] File upload for driver documents + company documents
- [ ] Image upload for user avatar
- [ ] Pagination components for all list views
- [ ] Search & filter components
- [ ] Loading skeletons for all pages
- [ ] Error boundary components
- [ ] 404 and error pages
- [ ] PWA support (optional)

---

## 11. File Structure (Target)

```
src/
├── lib/
│   ├── api.ts               # Axios instance + API types    [DONE]
│   ├── mockData.ts           # Mock data (to be phased out)
│   └── utils.ts              # Utility functions
├── contexts/
│   └── AuthContext.tsx        # Auth state + API calls        [DONE]
├── hooks/
│   ├── useRides.ts           # React Query hooks for rides
│   ├── usePayments.ts        # React Query hooks for payments
│   ├── useReviews.ts         # React Query hooks for reviews
│   ├── useNotifications.ts   # React Query hooks for notifications
│   ├── useDrivers.ts         # React Query hooks for drivers
│   ├── useVehicles.ts        # React Query hooks for vehicles
│   └── useAdmin.ts           # React Query hooks for admin endpoints
├── components/
│   ├── auth/
│   │   └── ProtectedRoute.tsx                                 [DONE]
│   ├── layout/
│   │   ├── PublicLayout.tsx
│   │   ├── DashboardLayout.tsx                                [DONE]
│   │   └── AdminLayout.tsx   # New — admin dashboard layout
│   └── ui/                   # shadcn-ui components
├── pages/
│   ├── auth/
│   │   ├── LoginPage.tsx                                      [DONE]
│   │   ├── RegisterPage.tsx                                   [DONE]
│   │   ├── LogoutPage.tsx                                     [DONE]
│   │   ├── VerifyEmailPage.tsx        # New
│   │   └── ResetPasswordPage.tsx      # New
│   ├── dashboard/
│   │   ├── ProfilePage.tsx                                    [DONE]
│   │   ├── SubscriptionPage.tsx
│   │   └── DashboardPages.tsx         # Split into individual files
│   └── admin/
│       ├── AdminDashboardPage.tsx     # New
│       ├── UserManagementPage.tsx     # New
│       ├── DriverVerificationPage.tsx # New
│       ├── CompanyVerificationPage.tsx# New
│       ├── RidesManagementPage.tsx    # New
│       ├── PricingRulesPage.tsx       # New
│       ├── ContentManagementPage.tsx  # New
│       ├── PaymentsPage.tsx           # New
│       ├── AuditLogsPage.tsx          # New
│       └── SettingsPage.tsx           # New
└── App.tsx                                                    [EXISTS]
```

---

## 12. Environment Variables

### Frontend (`.env`)
```
VITE_API_BASE_URL=http://localhost:8000
```

### Backend (`.env`)
```
FRONTEND_URL=http://localhost:8080
```

### Backend CORS (`config/cors.php`)
Already updated to include `http://localhost:8080`.
