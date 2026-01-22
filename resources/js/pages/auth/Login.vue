<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import { Form, Head } from '@inertiajs/vue3';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();
</script>

<template>
    <AuthBase
     title="تسجيل الدخول إلى حسابك"
        description=" أدخل بريدك الإلكتروني وكلمة المرور للولوج إلى حسابك "
    >
        <Head title="تسجيل الدخول" />

        <!-- شعار المشروع -->
      <!--  
         <div class="w-full flex justify-center mb-8">
            <img
                src="/images/jalili-logo.png"
                alt="Jalili Logo"
                class="w-28 h-auto opacity-95"
            />
        </div
        --> 

        <div
            v-if="status"
            class="mb-4 text-center text-sm font-medium text-green-600"
        >
            {{ status }}
        </div>

        <Form
            v-bind="store.form()"
            :reset-on-success="['password']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-6">

                <!-- البريد الإلكتروني -->
                <div class="grid gap-2 text-right">
                    <Label for="email">البريد الإلكتروني</Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="email"
                        placeholder="example@email.com"
                        class="text-right"
                    />
                    <InputError :message="errors.email" />
                </div>

                <!-- كلمة المرور -->
                <div class="grid gap-2 text-right">
                    <div class="flex items-center justify-between">
                        <Label for="password">كلمة المرور</Label>
                        <TextLink
                            v-if="canResetPassword"
                            :href="request()"
                            class="text-sm"
                            :tabindex="5"
                        >
                            نسيت كلمة المرور؟
                        </TextLink>
                    </div>
                    <Input
                        id="password"
                        type="password"
                        name="password"
                        required
                        :tabindex="2"
                        autocomplete="current-password"
                        placeholder="********"
                        class="text-right"
                    />
                    <InputError :message="errors.password" />
                </div>

                <!-- تذكرني -->
                <div class="flex items-center justify-end gap-2 text-right">
                    <Label for="remember" class="flex items-center gap-2">
                        <Checkbox id="remember" name="remember" :tabindex="3" />
                        <span>تذكرني</span>
                    </Label>
                </div>

                <!-- زر تسجيل الدخول -->
                <Button
                    type="submit"
                    class="mt-4 w-full bg-[#0A5A55] hover:bg-[#084b47] text-white"
                    :tabindex="4"
                    :disabled="processing"
                    data-test="login-button"
                >
                    <Spinner v-if="processing" />
                    تسجيل الدخول
                </Button>
            </div>

            <!-- تسجيل حساب جديد -->
            <div
                class="text-center text-sm text-muted-foreground"
                v-if="canRegister"
            >
                ليس لديك حساب؟
                <TextLink :href="register()" :tabindex="5">إنشاء حساب</TextLink>
            </div>
        </Form>
    </AuthBase>
</template>
