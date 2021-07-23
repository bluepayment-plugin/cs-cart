# Instrukcja instalacji oraz obsługi wtyczki dla platformy CS-Cart

## Podstawowe informacje

Wymagania systemowe 
Do prawidłowego działania dodatku potrzebna jest odpowiednia konfiguracja modułów PHP na serwerze, na którym jest zainstalowany CS-Cart. Dodatek wymaga aktywacji następujących rozszerzeń: 
- xmlWriter 
- xmlReader 
- iconv 
- mbstring 
- hash 

Ponadto na serwerze powinien być zainstalowany PHP w wersji co najmniej 5.5. W przypadku, gdy któryś z powyższych warunków nie jest spełniony, instalator poinformuje o tym podczas instalacji oraz nie pozwoli na zainstalowanie dodatku. 


## Instalacja dodatku 

W celu zainstalowania dodatku należy pobrać paczkę instalacyjną dodatku. Następnie w panelu administracyjnym przechodzimy do sekcji **„Dodatki/Zarządzanie dodatkami”** (górne menu) i klikamy w przycisk oznaczony znakiem [**+**]. 

Zostanie wyświetlone nowe okno popup, w którym należy kliknąć w przycisk **„Dysk lokalny”** i wybrać pobraną wcześniej paczkę instalacyjną dodatku. Po wybraniu paczki klikamy w przycisk **„Załaduj i zainstaluj”**. 

 Po zainstalowaniu dodatku odnajdujemy go na liście dostępnych modułów i, wybierając z sekcji narzędzi opcję **Ustawienia**, konfigurujemy dodatek oraz go aktywujemy. 
 
 ## Konfiguracja dodatku 

### Ustawienia ogólne dodatku 

**Wybór kanału płatności podczas składania zamówienia (WhiteLabel)**: gdy wybierzemy „**Tak**”, Klient będzie miał możliwość wybrania kanału płatności podczas składania zamówienia, w przeciwnym razie taka funkcjonalność będzie dostępna dopiero po złożeniu zamówienia, gdy Klient zostanie automatycznie przeniesiony na stronę serwisu Blue Media. 

**Grupuj po typie bramki**: przy ustawieniu tej opcji na „**Tak**” (w przypadku, gdy postanowimy, iż wybór kanału płatności będzie odbywał się podczas składania zamówienia) lista instrumentów płatniczych dostępnych w systemie płatności Blue Media będzie pogrupowana po typie bramki. Przy odznaczonym grupowaniu kanały płatności będą prezentowane bez uporządkowania według typu. 

![Wybór kanału płatności podczas składania zamówienia](https://user-images.githubusercontent.com/87177993/126763397-ef9b899e-03ea-4ba6-ae1f-8fdbe0ff0ae2.jpg)

Kolejną zakładką w konfiguracji dodatku jest zakładka „**Zamówienia cykliczne**”, zwane też „abonamentowymi”. Dotyczy ona ustawień sklepu odnoszących się do zamówień, za których płatność może być pobierana „automatycznie” co jakiś czas. 

![Zamówienia cykliczne](https://user-images.githubusercontent.com/87177993/126763537-08f70dde-7fe1-490d-b25b-86b7ff965225.jpg)

Pierwsza z opcji umożliwia globalne włączenie bądź wyłączenie funkcjonalności zamówień abonamentowych. Druga opcja („**Wyłącz dla zamówień ze zniżkami (promocjami)**”) uniemożliwia stworzenie zamówienia cyklicznego na podstawie złożonego już zamówienia, w którym – ze względu np. na reguły zastosowanych promocji – ceny produktów nie są cenami standardowymi. 

Istnieje jeszcze jedna metoda zablokowania możliwości utworzenia zamówienia cyklicznego. Jeśli nie chcemy, aby jakiś produkt kupiony przez Klienta mógł być objęty zamówieniem abonamentowym, możemy wykluczyć go z tej możliwości edytując dany produkt. Wystarczy na stronie edycji produktu w zakładce „**Dodatki**” ustawić w sekcji „Blue Media” wartość pola „**Wyklucz produkt z zakupów cyklicznych**” na „**Nie**”. Jeśli ów produkt znajdzie się w dowolnym zamówieniu, nie będzie dostępna opcja stworzenia zamówienia cyklicznego. 

![Wyklucz produkt z zakupów cyklicznych](https://user-images.githubusercontent.com/87177993/126763771-35ca0234-3489-4029-83a9-486cca348cad.png)


