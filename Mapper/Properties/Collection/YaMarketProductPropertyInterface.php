<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
 *  
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *  
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *  
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

namespace BaksDev\Yandex\Market\Products\Mapper\Properties\Collection;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('baks.ya.product.property')]
interface YaMarketProductPropertyInterface
{
    /**
     * Возвращает ключ (индекс в массиве)
     */
    public function getIndex(): string;

    /**
     * Метод возвращает результат, присваиваемый к значению
     */
    public function getData(array $data): mixed;

    /**
     * Возвращает значение по умолчанию
     */
    public function default(): ?string;

    /**
     * Отобразить для заполнения в общих настройках
     */
    public function isSetting(): bool;

    /**
     * Отобразить для заполнения в настройках категории
     */
    public function isCard(): bool;

    /**
     * Обязательный для заполнения
     */
    public function required(): bool;

    /**
     * Массив допустимых значений
     */
    public function choices(): ?array;

    /**
     * Сортировка (чем выше число - тем первым в итерации будет значение)
     */
    public static function priority(): int;

    /**
     * Проверяет, относится ли статус к данному объекту
     */
    public static function equals(string $status): bool;
}
