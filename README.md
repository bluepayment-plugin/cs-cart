# Instrukcja dla modułu CS-Cart: "Płatności online Autopay"

## Podstawowe informacje

Jeżeli jeszcze nie masz wtyczki, możesz ją pobrać [tutaj.](https://github.com/bluepayment-plugin/cs-cart/archive/refs/heads/main.zip)

### Wymagania systemowe 
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

**Wybór kanału płatności podczas składania zamówienia (WhiteLabel)**: gdy wybierzemy „**Tak**”, Klient będzie miał możliwość wybrania kanału płatności podczas składania zamówienia, w przeciwnym razie taka funkcjonalność będzie dostępna dopiero po złożeniu zamówienia, gdy Klient zostanie automatycznie przeniesiony na stronę serwisu Autopay. 

**Grupuj po typie bramki**: przy ustawieniu tej opcji na „**Tak**” (w przypadku, gdy postanowimy, iż wybór kanału płatności będzie odbywał się podczas składania zamówienia) lista instrumentów płatniczych dostępnych w systemie płatności Autopay będzie pogrupowana po typie bramki. Przy odznaczonym grupowaniu kanały płatności będą prezentowane bez uporządkowania według typu. 
![Wybór kanału płatności podczas składania zamówienia](https://github.com/bluepayment-plugin/cs-cart/assets/111113369/fe79ef72-4248-4967-8b47-314be2cbb935)


Kolejną zakładką w konfiguracji dodatku jest zakładka „**Zamówienia cykliczne**”, zwane też „abonamentowymi”. Dotyczy ona ustawień sklepu odnoszących się do zamówień, za których płatność może być pobierana „automatycznie” co jakiś czas. 

![Zamówienia cykliczne](https://github.com/bluepayment-plugin/cs-cart/assets/111113369/ecdd173d-c885-49e6-9838-ad7b5dc2648b)


Pierwsza z opcji umożliwia globalne włączenie bądź wyłączenie funkcjonalności zamówień abonamentowych. Druga opcja („**Wyłącz dla zamówień ze zniżkami (promocjami)**”) uniemożliwia stworzenie zamówienia cyklicznego na podstawie złożonego już zamówienia, w którym – ze względu np. na reguły zastosowanych promocji – ceny produktów nie są cenami standardowymi. 

Istnieje jeszcze jedna metoda zablokowania możliwości utworzenia zamówienia cyklicznego. Jeśli nie chcemy, aby jakiś produkt kupiony przez Klienta mógł być objęty zamówieniem abonamentowym, możemy wykluczyć go z tej możliwości edytując dany produkt. Wystarczy na stronie edycji produktu w zakładce „**Dodatki**” ustawić w sekcji „Autopay” wartość pola „**Wyklucz produkt z zakupów cyklicznych**” na „**Tak**”. Jeśli ów produkt znajdzie się w dowolnym zamówieniu, nie będzie dostępna opcja stworzenia zamówienia cyklicznego. 

![Wyklucz produkt z zakupów cyklicznych](https://github.com/bluepayment-plugin/cs-cart/assets/111113369/1745f2d1-d69a-4057-b360-0f2ed0f45387)

Więcej informacji o zamówieniach cyklicznych znajduje się w rozdziale "Działanie dodatku" -> "Zamówienia cykliczne". 

### Ustawienia metody płatności 

Po instalacji dodatku zostaną automatycznie utworzone i wstępnie skonfigurowane cztery nowe metody płatności: „System płatności online Autopay”, „Apple Pay”, „Płatność BLIK” oraz „VISA Mobile”. Aby stały się one dostępne w sklepie, konieczne jest dokończenie ich konfiguracji. 

W panelu administracyjnym przechodzimy do sekcji Metody płatności (menu: **Administracja / Metody płatności**), a następnie klikamy w nazwę metody płatności. 

![Metody płatności](https://github.com/bluepayment-plugin/cs-cart/assets/111113369/158c0a3b-009b-4961-86bc-57d432cb6cb9)


 Wyświetli się nowa strona z konfiguracją płatności. Przechodzimy w niej do zakładki „**Konfiguruj**”, gdzie możemy dokonać szczegółowych ustawień bramki płatności. 
 
![Konfiguruj](https://github.com/bluepayment-plugin/cs-cart/assets/111113369/ce3b61e7-f1b3-4286-9f49-8de49d624706)


Podstawowa konfiguracja, jaką należy wykonać, to wprowadzenie danych dotyczących ustawień konta Autopay. Zgodnie z danymi jakie otrzymaliśmy wprowadzamy: „**ID usługi**”, „**Klucz konfiguracyjny (hash)**”, „**Separator**” oraz „**Algorytm**”, jakim jest kodowany hash (domyślnie SHA 256). Dodatkowe opcje, jakie możemy ustawić, to: 

- „**Tryb pracy**”: określa, czy konfigurujemy testowy serwis płatności (a więc możemy dowolnie testować funkcjonalności dodatku), czy serwis produkcyjny. 
- W polu „**Prefix opisu zamówienia**” istnieje możliwość ustawienia tekstu, który będzie wyświetlany przed numerem zamówienia w danych, jakie są wysyłane do serwisu Autopay. 
- „**ID bramki**” - identyfikator domyślnego kanału płatności (wartość opcjonalna). W metodzie płatności „**Apple Pay**” wartość tego parametru jest już wstępnie zdefiniowana. 

 Bardzo ważnym etapem konfiguracji jest przeprowadzenie odpowiedniego mapowania statusów zamówień dostępnych w sklepie CS-Cart na statusy serwisu płatności. Można to wykonać w sekcji „Mapowanie statusów Autopay => CS-Cart”. Przykładową konfigurację możemy zobaczyć na poniższym obrazku. 

![Mapowanie statusów Autopay](https://github.com/bluepayment-plugin/cs-cart/assets/111113369/5318f85b-c5c6-4b44-83c0-b21ac5fa6083)

Na samym dole zakładki „Konfiguruj” znajdują się automatycznie wygenerowane adresy URL do konfiguracji bramki płatności w Systemie Płatności Online Autopay. 

![Adresy URL do konfiguracji bramki płatności](https://user-images.githubusercontent.com/87177993/126765735-019dfe85-b1b2-495d-84a6-a96cf19e8ece.jpg)

Czynności opisane powyżej powtarzamy dla każdej z trzech predefiniowanych metod płatności Autopay. 

Po skończonej konfiguracji, jeśli metody płatności Autopay nie są aktywne, włączamy je wywołując odpowiednie polecenie w menu podręcznym. 

![Aktywacja](https://user-images.githubusercontent.com/87177993/126765846-38f889d3-ddf3-451f-a650-8550ae66278c.jpg)

Istnieje również możliwość stworzenia dodatkowych metod płatności dla ustalonych kanałów płatności, np. dla kart kredytowych. Klient po wybraniu takiej metody płatności jest automatycznie przenoszony do ustalonego kanału płatności (nie musi dokonywać wyboru kanału płatności w kolejnym kroku lub na liście dostępnych kanałów wyświetlonej poniżej metod płatności). Aby utworzyć nową metodę płatności należy: 

1.	W menu „**Metody płatności**” kliknąć przycisk „**Dodaj metodę płatności**”: 

![Dodaj metodę płatności](https://user-images.githubusercontent.com/87177993/126765980-a4d731c7-bc60-4616-8192-b5bd08126976.jpg)

2. W zakładce „**Główne**” wyświetlonego formularza określić nazwę metody płatności oraz wybrać serwis obsługujący "BlueMedia": 

![Nazwa metody płatności](https://user-images.githubusercontent.com/87177993/126766105-1bcbc819-fc9a-4764-baf9-73e41f766023.jpg)

3. Można dodatkowo ustawić logotyp metody płatności wgrywając obrazek odpowiadający danemu kanałowi płatności:  

![Ustawienie logotypu metody płatności](https://user-images.githubusercontent.com/87177993/126766231-ff2424a9-d01b-4ea8-a339-9161e2f01806.jpg)

Link do strony zawierającej logotypy do pobrania można znaleźć w popupie sekcji konfiguracji dodatku.

4.	W 	zakładce 	„**Konfiguruj**” 	należy 	ustawić 	konfigurację 	metody 	płatności analogicznie jak dla pozostałych metod płatności Autopay. Dodatkowo należy ustawić wartość pola „**ID bramki**” podając identyfikator wybranego kanału płatności (np. dla kart kredytowych – wartość 1500). Lista identyfikatorów kanałów płatności znajduje się w dokumentacji integracji bramki płatności Autopay. 

### Ustawienia dotyczące produktów 

Jeśli w konfiguracji dodatku mamy włączoną funkcjonalność zamówień cyklicznych i chcemy, by Klienci nie mieli możliwości kupowania w abonamencie określonych towarów dostępnych w ofercie sklepu, istnieje możliwość wykluczenia ich z mechanizmu zamówień cyklicznych. Działa to na takiej zasadzie, że jeśli w zamówieniu Klienta znajdzie się chociaż jeden produkt wykluczony z zamówień cyklicznych, system blokuje możliwość utworzenia takiego zamówienia. Na poniższym obrazku można zobaczyć, w jaki sposób wykluczyć produkt z zamówień cyklicznych podczas jego edycji.

![Wyklucz produkt z zakupów cyklicznych](https://github.com/bluepayment-plugin/cs-cart/assets/111113369/3118be9e-2831-4d4f-b255-7549a274ee1a)



## Działanie dodatku 

### Wybór formy płatności przy składaniu zamówienia 

Klient podczas składania zamówienia może wybrać jedną z dostępnych form płatności. Jeśli skonfigurowaliśmy poprawnie nasz dodatek, na liście form płatności pojawi się pozycja „**System płatności Autopay**”. Po jej zaznaczeniu pod listą opcji płatności może pojawić się dodatkowa sekcja z listą metod płatności udostępnianych przez serwis Autopay. Jest to zależne od konfiguracji dodatku. 

![System płatności Autopay](https://github.com/bluepayment-plugin/cs-cart/assets/111113369/87b0e6bb-870f-48fc-9f35-48c04d8f9669)


W przypadku, gdy w ustawieniach dodatku nie wybraliśmy opcji grupowania po typie bramki, lista ta może wyglądać trochę inaczej:

![Grupowanie metod płatnośc](https://github.com/bluepayment-plugin/cs-cart/assets/111113369/55676302-9ee2-4b3d-a327-ca736440f367)

Wybranie odpowiedniej formy płatności zaznaczane jest odpowiednią ramką. W przypadku kliknięcia w płatność BLIK’iem, poniżej listy pojawi się dodatkowe pole umożliwiające wprowadzenie kodu BLIK (Jeśli na liście nie ma tej formy płatności, skontaktuj się ze swoim opiekunem biznesowym Autopay).

### Informacje o płatnościach 

Po dokonaniu przez Klienta płatności za swoje zamówienie, Administrator sklepu ma możliwość monitorowania na bieżąco statusu płatności. Statusy zamówienia zmieniane są zgodnie z konfiguracją mapowań, opisanych w rozdziale Konfiguracja dodatku -> Ustawienia metody płatności. Gdy zamówienie jest już w pełni opłacone (status autoryzacji transakcji ma wartość SUCCESS), możliwe jest wykonanie zwrotu gotówki. 

#### Zwroty

W przypadku opłaconych zamówień istnieje możliwość wykonania zwrotu wpłaconych przez Klienta pieniędzy. Zwrot może być wykonany w całości lub częściowo. W celu zrobienia zwrotu wpłaty, należy kliknąć na przycisk „**Zwrot płatności**”: 

![Zwrot płatności](https://user-images.githubusercontent.com/87177993/126768616-0dd0cae5-62a7-40af-bdaf-52e683f4a8d2.jpg)

Po kliknięciu w przycisk, wyświetli się dodatkowe pytanie zabezpieczające, czy faktycznie chcemy wykonać tę operację. Po twierdzącej odpowiedzi może pojawić się jeden z dwóch popupów. W przypadku, gdy saldo na koncie Autopay jest niewystarczające, nie można wykonać zwrotu. Zostanie wówczas wyświetlona informacja taka, jak na poniższym obrazku. 

![Obecnie Twoje saldo wynosi 0](https://user-images.githubusercontent.com/87177993/126768781-f4a73e66-de50-4392-8823-15eb084684e4.jpg)

Gdy saldo jest dodatnie, zamiast powyższej informacji pojawia się okno z możliwością wprowadzenia wartości, jaką chcemy zwrócić. Domyślnie jest wpisana najwyższa wartość kwotowa, jaka może zostać zwrócona Klientowi (zwykle równa wartości zamówienia). 

![Zwrot płatności - kwota](https://user-images.githubusercontent.com/87177993/126768908-81a3fc71-be59-41c6-9202-35ff07634030.jpg)

Po poprawnym wykonaniu zwrotu na stronie szczegółów zamówienia zostanie wyświetlony panel informacji dotyczących operacji związanych ze zwrotem. Można się z nich dowiedzieć, kiedy zostały wykonane zwroty, przez kogo oraz jaka była zwracana wartość (przykładową listę można zobaczyć na obrazku poniżej). 

![Panel informacji dotyczących operacji związanych ze zwrotem](https://user-images.githubusercontent.com/87177993/126769040-af1589e1-5845-4d9f-a47f-018c6b73777b.jpg)

W celu znalezienia zamówień, w których został wykonany zwrot płatności w systemie Autopay, należy skorzystać z zaawansowanej wyszukiwarki zamówień. W panelu administracyjnym przechodzimy do sekcji Zamówienia (w menu „**Zamówienia / Zobacz zamówienia**” i klikamy w opcję „**Wyszukiwanie zaawansowane**” (prawa kolumna na dole). Zostanie wyświetlane nowe okno typu popup, w którym zaznaczamy opcję „**Zwrot płatności (Autopay)**” i klikamy w przycisk „**Szukaj**”. Pojawi się lista tylko tych zamówień, w których dokonano zwrotu płatności. 

### Zamówienia cykliczne 

W przypadku, gdy włączymy w ustawieniach dodatku obsługę płatności automatycznych, Klient po złożeniu i opłaceniu zamówienia ma możliwość utworzenia na jego podstawie nowego, cyklicznego zamówienia. W takim przypadku płatność za zamówienie będzie ściągana przez administratora / Dział obsługi zamówień sklepu manualnie, bez udziału Klienta (w szczegółach zamówienia w panelu administracyjnym) lub też automatycznie (wymagana ingerencja ze strony administratora serwera). 

Poniżej zamieszczony został przykładowy zrzut ekranu, pokazujący w jaki sposób Klient może utworzyć nowe zamówienie cykliczne. 

![w jaki sposób Klient może utworzyć nowe zamówienie cykliczne](https://user-images.githubusercontent.com/87177993/126770276-a3b7fa3a-a114-40f6-9593-589ef0865500.jpg)

Po złożeniu zamówienia oraz jego opłaceniu, w szczegółach zamówienia pojawi się nowa opcja „**Stwórz zamówienie cykliczne**” (rysunek powyżej). Kliknięcie w ów link spowoduje utworzenie kopii bieżącego zamówienia. Proces składania zamówienia jest niemal identyczny jak w przypadku zwykłego zamówienia. Jedyną różnicą jest to, że Klient nie może samemu wybrać form ani metod płatności. Forma płatności jest narzucona odgórnie, a odpowiedni komunikat dotyczący zamówienia abonamentowego wyświetlany. Po pozytywnym utworzeniu zamówienia cyklicznego w zamówieniu bazowym znika wcześniej dostępna opcja umożliwiająca utworzenie zamówienia cyklicznego. Natomiast w nowym zamówieniu pojawia się w tym samym miejscu opcja umożliwiająca dezaktywację utworzonej funkcjonalności (administrator sklepu nie będzie miał możliwości wywołania pobrania należności). 

#### Zamówienia abonamentowe od strony panelu administracyjnego 

W panelu administracyjnym po przejściu do sekcji Zamówienia, wyświetlana jest lista złożonych zamówień. Dzięki informacjom w nowej kolumnie „**Zamówienia abonamentowe**”, w łatwy sposób możemy określić, które zamówienia są zamówieniami cyklicznymi. 

![Zamówienia abonamentowe](https://user-images.githubusercontent.com/87177993/126770484-778d19a3-d59e-45a9-8526-b405573e1999.jpg)

Dodatkowo w tej kolumnie jest pokazywany bieżący status takiego zamówienia (*pending* - w trakcie tworzenia, *activated* - zamówienie cykliczne potwierdzone od strony serwisu Autopay, *disactivated* - funkcjonalność wyłączona przez Klienta lub Administratora sklepu). 
 
Pobierając opłatę za zamówienie abonamentowe automatycznie tworzone jest nowe zamówienie (będące kopią zamówienia bazowego), do którego ściągana jest odpowiednia należność. Informację o tym, iż dane zamówienie jest kopią zamówienia abonamentowego zobaczymy w tej samej kolumnie. Przy takich zamówieniach będzie wyświetlany tekst “**Zamówienie bazowe: X**” (gdzie X to numer zamówienia, do którego się ono odnosi). 

Możliwe jest także skorzystanie z wyszukiwania zaawansowanego zamówień - zostało tam dodane nowe pole typu checkbox, umożliwiające okrojenie wyników wyszukiwania do zamówień cyklicznych. 

 Przeglądając szczegóły zamówienia także mamy dostęp do informacji związanych z zamówieniem cyklicznym. W prawej kolumnie wyświetlane są dane na temat statusu płatności abonamentowej. Jest także dostęp do opcji dezaktywacji zamówienia cyklicznego oraz do pobrania należności (abonamentu). 

![Status płatności abonamentowej](https://user-images.githubusercontent.com/87177993/126770801-417b3dd6-e9b5-4639-8ff9-49e1ed8cc65e.jpg)

Dodatkowo w sekcji “**Dodatki**” zamówienia bazowego, wyświetlana jest lista wszystkich zamówień (kopii) powiązanych z danym zamówieniem, dla których została pobrana należność. Przedstawia to obrazek poniżej. 

![Lista wszystkich zamówień powiązanych z danym zamówieniem](https://user-images.githubusercontent.com/87177993/126770919-beff23b8-d99f-4cb1-97c3-56365f4b1ff1.jpg)

W przypadku zamówień będących kopiami zamówienia bazowego, w tej samej zakładce “**Dodatki**” można znaleźć informację dotyczącą z którym zamówieniem jest ono powiązane (obrazek poniżej). 

![Zakupy cykliczne - informacja o zamówieniu bazowym](https://user-images.githubusercontent.com/87177993/126771022-ee1b7330-64c7-4643-a83e-03af329e3b00.jpg)

## Dodatkowe informacje

### Aktualizacja dodatku

W przypadku aktualizacji dodatku w CS-Cart z wersji mniejszej lub równej niż v2.8.4 do wersji v.2.8.5 są wymagane dodatkowe czynności do zrealizowania.

Po pobraniu nowej wersji dodatku, należy wgrać pliki dodatku na swój serwer zastępując pliki wcześniejszej wersji. Następnym krokiem jest wywołanie opcji "Odświeżenia" dodatku (na stronie z listą dodatków, przy dodatku "2LM: System płatności BlueMedia", z dropdown'a dostępnego obok nazwy należy wybrać opcję "Odśwież").

![Wybór opcji Odśwież](https://github.com/bluepayment-plugin/cs-cart/assets/111113369/cab3a8e4-bbf3-40c4-9b7f-6354cca5673a)

W przypadku, gdy opcja "Odśwież" nie będzie widoczna w dropdown'ie, należy w pliku config.php Cs-Carta, znaleźć linię z kodem

//define('DEVELEPMENT', true);

i usunąć z przodu znaki '//'. Następnie należy odświeżyć stronę z listą dodatków i wybrać opcję "Odśwież" opisaną powyżej.

Po skończonej aktualizacji dodatek zmieni nazwę na "2LM: Płatności online Autopay". W przypadku, gdy była konieczność modyfikacji pliku config.php, należy cofnąć wcześniej wykonane zmiany.

W przypadku świeżej instalacji dodatku w wersji v2.8.5 (lub wyższej), powyższe czynności nie są wymagane.
