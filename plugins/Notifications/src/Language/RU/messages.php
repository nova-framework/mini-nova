<?php

return array (
  'Hello!' => '',
  'Hello! }}
                                                @endif
                                            @endif
                                        </h1>

                                        <!-- Intro -->
                                        @foreach ($introLines as $line)
                                            <p style="{{ $style[\'paragraph\'] }}">
                                                {{ $line }}
                                            </p>
                                        @endforeach

                                        <!-- Action Button -->
                                        @if (isset($actionText))
                                            <table style="{{ $style[\'body_action\'] }}" align="center" width="100%" cellpadding="0" cellspacing="0">
                                                <tr>
                                                    <td align="center">
                                                        @php
                                                            switch ($level) {
                                                                case \'success\':
                                                                    $actionColor = \'button--green\';
                                                                    break;
                                                                case \'error\':
                                                                    $actionColor = \'button--red\';
                                                                    break;
                                                                default:
                                                                    $actionColor = \'button--blue\';
                                                            }
                                                        @endphp

                                                        <a href="{{ $actionUrl }}"
                                                            style="{{ $fontFamily }} {{ $style[\'button\'] }} {{ $style[$actionColor] }}"
                                                            class="button"
                                                            target="_blank">
                                                            {{ $actionText }}
                                                        </a>
                                                    </td>
                                                </tr>
                                            </table>
                                        @endif

                                        <!-- Outro -->
                                        @foreach ($outroLines as $line)
                                            <p style="{{ $style[\'paragraph\'] }}">
                                                {{ $line }}
                                            </p>
                                        @endforeach

                                        <!-- Salutation -->
                                        <p style="{{ $style[\'paragraph\'] }}">
                                            Regards,<br>{{ config(\'app.name' => '',
  'If you’re having trouble clicking the "{0}" button, copy and paste the URL below into your web browser:' => '',
  'Regards,' => '',
  'Whoops!' => '',
);
