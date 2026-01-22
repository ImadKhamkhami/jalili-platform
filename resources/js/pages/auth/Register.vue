<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { store } from '@/routes/register';
import { Form, Head } from '@inertiajs/vue3';
</script>

<template>
    <AuthBase
        title="إنشاء حساب جديد"
        description="أدخل معلوماتك لإنشاء حسابك في المنصة"
    >
        <Head title="إنشاء حساب" />

        <Form
            v-bind="store.form()"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-6 text-right">

                <div class="grid gap-2">
                    <Label for="name" class="font-medium">الاسم الكامل</Label>
                    <Input
                        id="name"
                        type="text"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="name"
                        name="name"
                        placeholder="أدخل اسمك الكامل"
                        class="text-right"
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email" class="font-medium">البريد الإلكتروني</Label>
                    <Input
                        id="email"
                        type="email"
                        required
                        :tabindex="2"
                        autocomplete="email"
                        name="email"
                        placeholder="example@email.com"
                        class="text-right"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="password" class="font-medium">كلمة المرور</Label>
                    <Input
                        id="password"
                        type="password"
                        required
                        :tabindex="3"
                        autocomplete="new-password"
                        name="password"
                        placeholder="أدخل كلمة المرور"
                        class="text-right"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation" class="font-medium">
                        تأكيد كلمة المرور
                    </Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        required
                        :tabindex="4"
                        autocomplete="new-password"
                        name="password_confirmation"
                        placeholder="أعد إدخال كلمة المرور"
                        class="text-right"
                    />
                    <InputError :message="errors.password_confirmation" />
                </div>

                <!-- زر إنشاء الحساب بألوان مشروعك -->
                <Button
                    type="submit"
                    class="mt-2 w-full bg-[#0A5A55] hover:bg-[#084b47] text-white"
                    tabindex="5"
                    :disabled="processing"
                    data-test="register-user-button"
                >
                    <Spinner v-if="processing" />
                    إنشاء الحساب
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground mt-2">
                لديك حساب بالفعل؟
                <TextLink :href="login()" :tabindex="6" class="text-[#0A5A55] font-semibold">
                    تسجيل الدخول
                </TextLink>
            </div>
        </Form>
    </AuthBase>
</template>
