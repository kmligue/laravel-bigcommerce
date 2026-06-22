export interface AppConfig {
  appName: string;
  stripeKey: string;
  storeHash: string | null;
}

declare global {
  interface Window {
    __APP__: AppConfig;
  }
}

export interface PlanStatus {
  is_subscribed: boolean;
  is_on_trial: boolean;
  current_plan: string | null;
  trial_ends_at: string | null;
  has_advanced_during_trial: boolean;
  post_trial_plan: string | null;
  plan_details: Record<string, unknown> | null;
}

export interface TrialNotice {
  show: boolean;
  is_on_trial: boolean;
  trial_ended: boolean;
  plan_expired: boolean;
  days_left: number;
  trial_ends_at: string | null;
  post_trial_plan: string;
  highest_plan: string;
  store_hash: string;
}

export interface StoreContext {
  app_name: string;
  store_hash: string;
  store_hash_short: string;
  plan_status: PlanStatus;
  trial_notice: TrialNotice;
  highest_plan: string | null;
  lowest_plan: string | null;
}

export interface PlanCard {
  key: string;
  name: string;
  price: number;
  features: string[];
  is_current: boolean;
  is_trial_plan: boolean;
  button_text: string;
  show_cancel: boolean;
}

export interface BillingData {
  plans: PlanCard[];
  has_active_subscription: boolean;
  is_on_trial: boolean;
  user_status: PlanStatus;
  current_plan: Record<string, unknown> | null;
  free_plan_id: string;
}

export interface Invoice {
  date: string;
  description: string;
}

export interface AdminStore {
  store_hash: string;
  store_hash_short: string;
  load_url: string;
  name: string;
  user_name: string;
  user_email: string;
  plan_label: string;
  plan_price: number | null;
  plan_id: string | null;
  install_date: string | null;
  trial_ends_at: string | null;
  on_trial: boolean;
  metadata: Record<string, unknown>;
  plan_options: { key: string; label: string; plan_id: string }[];
}

export interface AdminInstallsData {
  app_name: string;
  stores: AdminStore[];
  plans: { key: string; label: string; plan_id: string }[];
}
