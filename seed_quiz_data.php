<?php
// ============================================================
//  PLAY2REVIEW — Quiz Data Seeder
//  Visit: http://localhost/play2review/seed_quiz_data.php?token=seed2024
//  Inserts 5 questions per subject × category × level (1-10)
// ============================================================
include('configurations/configurations.php');

$token = $_GET['token'] ?? '';
if ($token !== 'seed2024') {
    die('<h2 style="color:red;font-family:sans-serif;">Access denied. Add ?token=seed2024 to the URL.</h2>');
}

$inserted = 0;
$skipped  = 0;
$errors   = [];

function ins($con, $subject, $level, $category, $q, $a, $b, $c, $d, $correct) {
    global $inserted, $skipped, $errors;
    $check = mysqli_query($con,
        "SELECT id FROM quizes WHERE subject_name='".mysqli_real_escape_string($con,$subject)."'
         AND quiz_level=$level
         AND category='".mysqli_real_escape_string($con,$category)."'
         AND question='".mysqli_real_escape_string($con,$q)."' LIMIT 1");
    if (mysqli_num_rows($check) > 0) { $skipped++; return; }
    $sql = "INSERT INTO quizes (subject_name,quiz_level,category,question,answer_a,answer_b,answer_c,answer_d,correct_answer_number)
            VALUES ('".mysqli_real_escape_string($con,$subject)."',$level,
                    '".mysqli_real_escape_string($con,$category)."',
                    '".mysqli_real_escape_string($con,$q)."',
                    '".mysqli_real_escape_string($con,$a)."',
                    '".mysqli_real_escape_string($con,$b)."',
                    '".mysqli_real_escape_string($con,$c)."',
                    '".mysqli_real_escape_string($con,$d)."',$correct)";
    if (mysqli_query($con,$sql)) $inserted++;
    else $errors[] = mysqli_error($con)." | Q: $q";
}

// Ensure category column exists
mysqli_query($con, "ALTER TABLE quizes ADD COLUMN IF NOT EXISTS category VARCHAR(100) DEFAULT '' AFTER quiz_level");


// ============================================================
// ENGLISH — Grammar & Language Structure
// ============================================================
// Level 1
ins($con,'english',1,'Grammar & Language Structure','Which sentence is correct?','She go to school.','She goes to school.','She going to school.','She gone to school.',2);
ins($con,'english',1,'Grammar & Language Structure','What is the plural of "child"?','Childs','Childes','Children','Childrens',3);
ins($con,'english',1,'Grammar & Language Structure','Which word is a noun?','Run','Happy','Book','Quickly',3);
ins($con,'english',1,'Grammar & Language Structure','Choose the correct article: ___ apple.','A','An','The','No article',2);
ins($con,'english',1,'Grammar & Language Structure','Which is a complete sentence?','Running fast.','The dog barked.','Under the tree.','A big red.',2);
// Level 2
ins($con,'english',2,'Grammar & Language Structure','Identify the verb: "The cat sleeps on the mat."','cat','sleeps','mat','The',2);
ins($con,'english',2,'Grammar & Language Structure','Which sentence uses correct punctuation?','Where are you going','Where are you going?','where are you going?','Where are you going!',2);
ins($con,'english',2,'Grammar & Language Structure','What type of noun is "happiness"?','Proper','Common','Abstract','Collective',3);
ins($con,'english',2,'Grammar & Language Structure','Choose the correct pronoun: "___ is my friend."','Him','He','His','Himself',2);
ins($con,'english',2,'Grammar & Language Structure','Which word is an adjective?','Slowly','Bright','Run','Eat',2);
// Level 3
ins($con,'english',3,'Grammar & Language Structure','Which sentence is in past tense?','She walks to school.','She walked to school.','She will walk to school.','She is walking to school.',2);
ins($con,'english',3,'Grammar & Language Structure','What is the subject in: "The birds sing beautifully."?','sing','beautifully','The birds','birds sing',3);
ins($con,'english',3,'Grammar & Language Structure','Which is a compound sentence?','She sings.','She sings and he dances.','Because she sings.','Singing loudly.',2);
ins($con,'english',3,'Grammar & Language Structure','Identify the adverb: "He runs quickly."','He','runs','quickly','runs quickly',3);
ins($con,'english',3,'Grammar & Language Structure','Which word is a conjunction?','But','Book','Blue','Bright',1);
// Level 4
ins($con,'english',4,'Grammar & Language Structure','Which sentence has correct subject-verb agreement?','The boys plays outside.','The boys play outside.','The boys is playing outside.','The boys was playing outside.',2);
ins($con,'english',4,'Grammar & Language Structure','What is the object in: "Maria reads a book."?','Maria','reads','a book','book',3);
ins($con,'english',4,'Grammar & Language Structure','Which is a complex sentence?','I eat and she drinks.','Although it rained, we played.','We played.','Rain and sun.',2);
ins($con,'english',4,'Grammar & Language Structure','Choose the correct form: "She is ___ than her sister."','tall','taller','tallest','most tall',2);
ins($con,'english',4,'Grammar & Language Structure','Which sentence is in future tense?','He ate lunch.','He eats lunch.','He will eat lunch.','He has eaten lunch.',3);
// Level 5
ins($con,'english',5,'Grammar & Language Structure','Identify the preposition: "The book is on the table."','book','is','on','table',3);
ins($con,'english',5,'Grammar & Language Structure','Which sentence uses passive voice?','The dog bit the man.','The man was bitten by the dog.','The man bit the dog.','The dog bites the man.',2);
ins($con,'english',5,'Grammar & Language Structure','What is a clause?','A single word','A group of words with subject and verb','A punctuation mark','A type of noun',2);
ins($con,'english',5,'Grammar & Language Structure','Which is a subordinating conjunction?','And','But','Although','Or',3);
ins($con,'english',5,'Grammar & Language Structure','Choose the correct form: "Neither the boys nor the girl ___ ready."','are','were','is','be',3);
// Level 6
ins($con,'english',6,'Grammar & Language Structure','Which sentence contains a dangling modifier?','Running fast, the finish line was reached.','Running fast, she reached the finish line.','She ran fast to the finish line.','The finish line was near.',1);
ins($con,'english',6,'Grammar & Language Structure','Identify the gerund: "Swimming is good exercise."','is','good','Swimming','exercise',1);
ins($con,'english',6,'Grammar & Language Structure','Which is correct? "There/Their/They\'re going home."','There','Their','They\'re','Theyre',3);
ins($con,'english',6,'Grammar & Language Structure','What is an infinitive?','A verb ending in -ing','To + base form of verb','Past tense verb','A helping verb',2);
ins($con,'english',6,'Grammar & Language Structure','Which sentence has a misplaced modifier?','She almost drove her kids to school every day.','She drove her kids to school almost every day.','Almost every day she drove her kids to school.','Every day she drove her kids to school.',1);
// Level 7
ins($con,'english',7,'Grammar & Language Structure','Which is an example of parallel structure?','She likes swimming, to run, and cycling.','She likes swimming, running, and cycling.','She likes to swim, running, and cycle.','She likes swim, run, and cycling.',2);
ins($con,'english',7,'Grammar & Language Structure','Identify the appositive: "My brother, a doctor, lives in Manila."','My brother','a doctor','lives','Manila',2);
ins($con,'english',7,'Grammar & Language Structure','Which sentence uses the subjunctive mood?','If I was rich, I would travel.','If I were rich, I would travel.','If I am rich, I will travel.','If I be rich, I travel.',2);
ins($con,'english',7,'Grammar & Language Structure','What is a participial phrase?','A phrase with a preposition','A phrase acting as a noun','A phrase with a participle modifying a noun','A phrase with an infinitive',3);
ins($con,'english',7,'Grammar & Language Structure','Choose the correct punctuation: "However ___ she refused."','However, she refused.','However; she refused.','However: she refused.','However she refused.',1);
// Level 8
ins($con,'english',8,'Grammar & Language Structure','Which sentence correctly uses a semicolon?','I went to the store; and bought milk.','I went to the store; I bought milk.','I went; to the store and bought milk.','I went to the store, I bought milk.',2);
ins($con,'english',8,'Grammar & Language Structure','Identify the absolute phrase: "The game over, the players left."','The game over','the players left','players left','The game',1);
ins($con,'english',8,'Grammar & Language Structure','Which is correct use of the colon?','She bought: apples, oranges, and grapes.','She bought the following: apples, oranges, and grapes.','She: bought apples, oranges, and grapes.','She bought apples: oranges, and grapes.',2);
ins($con,'english',8,'Grammar & Language Structure','What is an elliptical clause?','A clause with an ellipsis mark','A clause where words are omitted but understood','A very long clause','A clause with two subjects',2);
ins($con,'english',8,'Grammar & Language Structure','Which sentence avoids a comma splice?','I love reading, it is my hobby.','I love reading; it is my hobby.','I love reading it is my hobby.','I love, reading it is my hobby.',2);
// Level 9
ins($con,'english',9,'Grammar & Language Structure','Which correctly uses the Oxford comma?','I bought apples, oranges and grapes.','I bought apples, oranges, and grapes.','I bought apples oranges, and grapes.','I bought, apples, oranges, and grapes.',2);
ins($con,'english',9,'Grammar & Language Structure','Identify the type: "The more you practice, the better you get."','Simple','Compound','Complex','Compound-complex',3);
ins($con,'english',9,'Grammar & Language Structure','Which is a nominative absolute?','Running fast','The sun having set, we went home.','To run fast','Having run fast',2);
ins($con,'english',9,'Grammar & Language Structure','What is anaphora in grammar?','Repetition of a word at the beginning of successive clauses','A type of pronoun','A punctuation mark','A verb tense',1);
ins($con,'english',9,'Grammar & Language Structure','Which sentence uses the perfect progressive tense?','She has been studying for hours.','She studied for hours.','She studies for hours.','She will study for hours.',1);
// Level 10
ins($con,'english',10,'Grammar & Language Structure','Which is an example of a cleft sentence?','It was Maria who won the prize.','Maria won the prize.','The prize was won.','Who won the prize?',1);
ins($con,'english',10,'Grammar & Language Structure','Identify the rhetorical device: "Ask not what your country can do for you."','Metaphor','Chiasmus','Anaphora','Hyperbole',2);
ins($con,'english',10,'Grammar & Language Structure','Which correctly uses the subjunctive in a that-clause?','The teacher insisted that he was present.','The teacher insisted that he be present.','The teacher insisted that he is present.','The teacher insisted that he being present.',2);
ins($con,'english',10,'Grammar & Language Structure','What is a syndeton?','Omission of conjunctions','Use of multiple conjunctions','A type of clause','A punctuation style',2);
ins($con,'english',10,'Grammar & Language Structure','Which sentence demonstrates correct use of the past perfect?','Before she arrived, he left.','Before she arrived, he had left.','Before she arrived, he has left.','Before she arrived, he leaves.',2);


// ============================================================
// ENGLISH — Vocabulary
// ============================================================
// Level 1
ins($con,'english',1,'Vocabulary','What does "happy" mean?','Sad','Angry','Joyful','Tired',3);
ins($con,'english',1,'Vocabulary','Which word means "big"?','Small','Large','Thin','Short',2);
ins($con,'english',1,'Vocabulary','What is the opposite of "hot"?','Warm','Cool','Cold','Mild',3);
ins($con,'english',1,'Vocabulary','Which word means "to look at"?','Listen','See','Smell','Touch',2);
ins($con,'english',1,'Vocabulary','What does "fast" mean?','Slow','Quick','Quiet','Loud',2);
// Level 2
ins($con,'english',2,'Vocabulary','What does "ancient" mean?','New','Modern','Very old','Colorful',3);
ins($con,'english',2,'Vocabulary','Which word is a synonym of "brave"?','Cowardly','Fearful','Courageous','Timid',3);
ins($con,'english',2,'Vocabulary','What does "enormous" mean?','Tiny','Huge','Average','Narrow',2);
ins($con,'english',2,'Vocabulary','Which word means "to make better"?','Worsen','Improve','Ignore','Destroy',2);
ins($con,'english',2,'Vocabulary','What is an antonym of "generous"?','Kind','Giving','Selfish','Helpful',3);
// Level 3
ins($con,'english',3,'Vocabulary','What does "transparent" mean?','Opaque','Clear','Colorful','Solid',2);
ins($con,'english',3,'Vocabulary','Which word means "very tired"?','Energetic','Exhausted','Excited','Alert',2);
ins($con,'english',3,'Vocabulary','What does "predict" mean?','To look back','To forget','To say what will happen','To describe',3);
ins($con,'english',3,'Vocabulary','Which is a synonym of "angry"?','Happy','Furious','Calm','Pleased',2);
ins($con,'english',3,'Vocabulary','What does "fragile" mean?','Strong','Heavy','Easily broken','Flexible',3);
// Level 4
ins($con,'english',4,'Vocabulary','What does "ambiguous" mean?','Clear','Having more than one meaning','Definite','Simple',2);
ins($con,'english',4,'Vocabulary','Which word means "to officially end"?','Begin','Continue','Abolish','Extend',3);
ins($con,'english',4,'Vocabulary','What does "benevolent" mean?','Cruel','Kind and generous','Selfish','Angry',2);
ins($con,'english',4,'Vocabulary','Which is an antonym of "abundant"?','Plentiful','Scarce','Rich','Ample',2);
ins($con,'english',4,'Vocabulary','What does "eloquent" mean?','Speaking poorly','Speaking fluently and persuasively','Speaking quietly','Speaking rudely',2);
// Level 5
ins($con,'english',5,'Vocabulary','What does "pragmatic" mean?','Idealistic','Practical','Emotional','Theoretical',2);
ins($con,'english',5,'Vocabulary','Which word means "to make worse"?','Improve','Alleviate','Exacerbate','Resolve',3);
ins($con,'english',5,'Vocabulary','What does "meticulous" mean?','Careless','Very careful and precise','Hasty','Disorganized',2);
ins($con,'english',5,'Vocabulary','Which is a synonym of "ephemeral"?','Permanent','Lasting','Temporary','Eternal',3);
ins($con,'english',5,'Vocabulary','What does "verbose" mean?','Brief','Using too many words','Silent','Unclear',2);
// Level 6
ins($con,'english',6,'Vocabulary','What does "ubiquitous" mean?','Rare','Found everywhere','Hidden','Unique',2);
ins($con,'english',6,'Vocabulary','Which word means "to officially approve"?','Reject','Ratify','Deny','Oppose',2);
ins($con,'english',6,'Vocabulary','What does "tenacious" mean?','Giving up easily','Holding firmly to a purpose','Indifferent','Weak',2);
ins($con,'english',6,'Vocabulary','Which is an antonym of "loquacious"?','Talkative','Verbose','Taciturn','Chatty',3);
ins($con,'english',6,'Vocabulary','What does "ameliorate" mean?','To worsen','To improve','To ignore','To destroy',2);
// Level 7
ins($con,'english',7,'Vocabulary','What does "equivocate" mean?','To speak clearly','To use vague language to avoid commitment','To argue strongly','To agree',2);
ins($con,'english',7,'Vocabulary','Which word means "showing off knowledge"?','Humble','Pedantic','Modest','Ignorant',2);
ins($con,'english',7,'Vocabulary','What does "perfidious" mean?','Loyal','Treacherous','Honest','Reliable',2);
ins($con,'english',7,'Vocabulary','Which is a synonym of "recalcitrant"?','Obedient','Compliant','Stubborn','Agreeable',3);
ins($con,'english',7,'Vocabulary','What does "sycophant" mean?','A critic','A flatterer who seeks favor','A leader','An opponent',2);
// Level 8
ins($con,'english',8,'Vocabulary','What does "obfuscate" mean?','To clarify','To make unclear or confusing','To simplify','To explain',2);
ins($con,'english',8,'Vocabulary','Which word means "brief and to the point"?','Verbose','Loquacious','Laconic','Elaborate',3);
ins($con,'english',8,'Vocabulary','What does "inimical" mean?','Friendly','Harmful or hostile','Helpful','Neutral',2);
ins($con,'english',8,'Vocabulary','Which is an antonym of "magnanimous"?','Generous','Noble','Petty','Forgiving',3);
ins($con,'english',8,'Vocabulary','What does "propitious" mean?','Unfavorable','Giving a sign of success','Dangerous','Uncertain',2);
// Level 9
ins($con,'english',9,'Vocabulary','What does "solipsism" mean?','Belief that only oneself exists','Belief in others','A type of logic','A social theory',1);
ins($con,'english',9,'Vocabulary','Which word means "to formally renounce"?','Claim','Assert','Abjure','Affirm',3);
ins($con,'english',9,'Vocabulary','What does "perspicacious" mean?','Dull','Having a ready insight','Confused','Ignorant',2);
ins($con,'english',9,'Vocabulary','Which is a synonym of "tendentious"?','Impartial','Biased','Neutral','Objective',2);
ins($con,'english',9,'Vocabulary','What does "vituperate" mean?','To praise','To blame or abuse verbally','To ignore','To reward',2);
// Level 10
ins($con,'english',10,'Vocabulary','What does "apocryphal" mean?','Well-documented','Of doubtful authenticity','Historically accurate','Widely accepted',2);
ins($con,'english',10,'Vocabulary','Which word means "a strong dislike"?','Affinity','Antipathy','Empathy','Sympathy',2);
ins($con,'english',10,'Vocabulary','What does "encomium" mean?','A criticism','A formal expression of praise','A complaint','A warning',2);
ins($con,'english',10,'Vocabulary','Which is an antonym of "pusillanimous"?','Cowardly','Timid','Courageous','Fearful',3);
ins($con,'english',10,'Vocabulary','What does "logomachy" mean?','Love of words','An argument about words','A type of poem','A grammar rule',2);

// ============================================================
// ENGLISH — Reading Comprehension
// ============================================================
// Level 1
ins($con,'english',1,'Reading Comprehension','What is the main idea of a paragraph?','The last sentence','The most important point','A detail','The title',2);
ins($con,'english',1,'Reading Comprehension','What does "setting" mean in a story?','The characters','Where and when the story takes place','The problem','The solution',2);
ins($con,'english',1,'Reading Comprehension','What is a "character" in a story?','A place','A time','A person or animal in the story','An event',3);
ins($con,'english',1,'Reading Comprehension','What does "predict" mean when reading?','To summarize','To guess what will happen next','To find the main idea','To describe a character',2);
ins($con,'english',1,'Reading Comprehension','What is the "plot" of a story?','The setting','The characters','The sequence of events','The theme',3);
// Level 2
ins($con,'english',2,'Reading Comprehension','What is an inference?','A direct statement','A conclusion based on evidence','A summary','A question',2);
ins($con,'english',2,'Reading Comprehension','What does "context clue" mean?','A dictionary definition','Information in the text that helps understand a word','A grammar rule','A punctuation mark',2);
ins($con,'english',2,'Reading Comprehension','What is the "theme" of a story?','The main character','The setting','The central message or lesson','The plot',3);
ins($con,'english',2,'Reading Comprehension','What is a "summary"?','A long retelling','A brief statement of main points','A list of characters','A description of setting',2);
ins($con,'english',2,'Reading Comprehension','What does "cause and effect" mean?','Two unrelated events','Why something happened and what resulted','A type of character','A story structure',2);
// Level 3
ins($con,'english',3,'Reading Comprehension','What is the difference between fact and opinion?','Facts are false; opinions are true','Facts can be proven; opinions are personal views','Facts are opinions','There is no difference',2);
ins($con,'english',3,'Reading Comprehension','What is an "author\'s purpose"?','The setting of the story','Why the author wrote the text','The main character\'s goal','The plot twist',2);
ins($con,'english',3,'Reading Comprehension','What does "point of view" mean?','The setting','The perspective from which a story is told','The theme','The conflict',2);
ins($con,'english',3,'Reading Comprehension','What is "text structure"?','The font used','How a text is organized','The number of paragraphs','The title',2);
ins($con,'english',3,'Reading Comprehension','What is a "supporting detail"?','The main idea','Information that supports the main idea','The conclusion','The introduction',2);
// Level 4
ins($con,'english',4,'Reading Comprehension','What is "figurative language"?','Literal meaning','Language that uses figures of speech','Grammar rules','Punctuation',2);
ins($con,'english',4,'Reading Comprehension','What is a "simile"?','Comparing using "like" or "as"','A direct comparison','An exaggeration','A contradiction',1);
ins($con,'english',4,'Reading Comprehension','What does "tone" mean in a text?','The volume','The author\'s attitude toward the subject','The setting','The plot',2);
ins($con,'english',4,'Reading Comprehension','What is "mood" in literature?','The author\'s feeling','The feeling created in the reader','The character\'s emotion','The setting',2);
ins($con,'english',4,'Reading Comprehension','What is "foreshadowing"?','A summary','Hints about future events','A flashback','A description',2);
// Level 5
ins($con,'english',5,'Reading Comprehension','What is "irony"?','A direct statement','When the opposite of what is expected occurs','A type of metaphor','A story structure',2);
ins($con,'english',5,'Reading Comprehension','What is a "protagonist"?','The villain','The main character','A minor character','The narrator',2);
ins($con,'english',5,'Reading Comprehension','What does "implicit" mean?','Stated directly','Suggested but not stated','Obvious','Clear',2);
ins($con,'english',5,'Reading Comprehension','What is "bias" in a text?','Balanced view','Unfair preference for one side','A type of evidence','A summary',2);
ins($con,'english',5,'Reading Comprehension','What is "connotation"?','The dictionary meaning','The emotional meaning of a word','A grammar rule','A punctuation mark',2);
// Level 6
ins($con,'english',6,'Reading Comprehension','What is "denotation"?','The emotional meaning','The literal dictionary meaning','A figure of speech','An inference',2);
ins($con,'english',6,'Reading Comprehension','What is an "allegory"?','A short poem','A story with a hidden meaning','A type of essay','A grammar structure',2);
ins($con,'english',6,'Reading Comprehension','What does "synthesize" mean in reading?','To summarize one text','To combine information from multiple sources','To find the main idea','To make an inference',2);
ins($con,'english',6,'Reading Comprehension','What is "textual evidence"?','An opinion','Specific details from the text to support a claim','A summary','A prediction',2);
ins($con,'english',6,'Reading Comprehension','What is "characterization"?','The setting description','How an author develops a character','The plot structure','The theme',2);
// Level 7
ins($con,'english',7,'Reading Comprehension','What is "dramatic irony"?','When the reader knows something the character does not','When a character is dramatic','When the plot is ironic','When the setting is ironic',1);
ins($con,'english',7,'Reading Comprehension','What is a "motif"?','A recurring element in a story','The main character','The setting','The climax',1);
ins($con,'english',7,'Reading Comprehension','What does "ambiguity" mean in a text?','Clear meaning','Having more than one possible interpretation','A grammar error','A plot twist',2);
ins($con,'english',7,'Reading Comprehension','What is "stream of consciousness"?','A narrative technique showing a character\'s thoughts','A type of setting','A plot structure','A figure of speech',1);
ins($con,'english',7,'Reading Comprehension','What is "unreliable narrator"?','A narrator who tells the truth','A narrator whose credibility is compromised','A third-person narrator','An omniscient narrator',2);
// Level 8
ins($con,'english',8,'Reading Comprehension','What is "intertextuality"?','Writing about nature','Relationship between texts','A grammar concept','A type of irony',2);
ins($con,'english',8,'Reading Comprehension','What is "deconstruction" in literary analysis?','Building a story','Analyzing hidden assumptions in a text','Summarizing a text','Finding the main idea',2);
ins($con,'english',8,'Reading Comprehension','What does "hermeneutics" refer to?','Grammar study','Theory of text interpretation','Vocabulary study','Punctuation rules',2);
ins($con,'english',8,'Reading Comprehension','What is a "foil" character?','The main character','A character who contrasts with another to highlight traits','The villain','The narrator',2);
ins($con,'english',8,'Reading Comprehension','What is "catharsis" in literature?','A plot twist','Emotional release experienced by the audience','A type of irony','A narrative structure',2);
// Level 9
ins($con,'english',9,'Reading Comprehension','What is "epistolary" form?','A story told through letters or documents','A type of poem','A narrative technique','A grammar structure',1);
ins($con,'english',9,'Reading Comprehension','What is "polyphony" in a novel?','One dominant voice','Multiple independent voices or perspectives','A type of setting','A plot device',2);
ins($con,'english',9,'Reading Comprehension','What does "mise en abyme" mean?','A type of setting','A story within a story that mirrors the main narrative','A character technique','A grammar device',2);
ins($con,'english',9,'Reading Comprehension','What is "defamiliarization"?','Making familiar things seem strange to see them anew','A type of irony','A plot structure','A character technique',1);
ins($con,'english',9,'Reading Comprehension','What is "free indirect discourse"?','Direct speech','A blend of narrator and character voice','Third-person narration','First-person narration',2);
// Level 10
ins($con,'english',10,'Reading Comprehension','What is "narratology"?','The study of narrative structure','A type of poem','A grammar theory','A vocabulary technique',1);
ins($con,'english',10,'Reading Comprehension','What is "heteroglossia"?','A single language style','Diversity of voices and language styles in a text','A grammar concept','A type of irony',2);
ins($con,'english',10,'Reading Comprehension','What does "aporia" mean in literary theory?','A clear resolution','An irresolvable contradiction in a text','A type of metaphor','A narrative technique',2);
ins($con,'english',10,'Reading Comprehension','What is "palimpsest" as a literary concept?','A new text','A text that carries traces of earlier writing','A type of poem','A grammar structure',2);
ins($con,'english',10,'Reading Comprehension','What is "paratext"?','The main text','Elements surrounding the text (title, preface, etc.)','A type of character','A plot device',2);

// ============================================================
// ENGLISH — Literature
// ============================================================
// Level 1
ins($con,'english',1,'Literature','What is a "fairy tale"?','A true story','A story with magical elements','A poem','A play',2);
ins($con,'english',1,'Literature','What is a "fable"?','A long novel','A short story with a moral lesson','A poem','A biography',2);
ins($con,'english',1,'Literature','Who wrote "Romeo and Juliet"?','Charles Dickens','William Shakespeare','Mark Twain','Homer',2);
ins($con,'english',1,'Literature','What is a "poem"?','A long story','Writing that uses rhythm and imagery','A play','A biography',2);
ins($con,'english',1,'Literature','What is a "novel"?','A short story','A long fictional narrative','A poem','A play',2);
// Level 2
ins($con,'english',2,'Literature','What is a "haiku"?','A long poem','A 3-line Japanese poem with 5-7-5 syllables','A rhyming poem','A narrative poem',2);
ins($con,'english',2,'Literature','What is "personification"?','Giving human traits to non-human things','A type of rhyme','A story structure','A grammar rule',1);
ins($con,'english',2,'Literature','What is a "metaphor"?','A comparison using "like" or "as"','A direct comparison without "like" or "as"','An exaggeration','A contradiction',2);
ins($con,'english',2,'Literature','What is "alliteration"?','Repetition of vowel sounds','Repetition of consonant sounds at the start of words','A type of rhyme','A story structure',2);
ins($con,'english',2,'Literature','What is a "sonnet"?','A 14-line poem','A 10-line poem','A 20-line poem','A 5-line poem',1);
// Level 3
ins($con,'english',3,'Literature','What is "hyperbole"?','An understatement','An extreme exaggeration','A comparison','A contradiction',2);
ins($con,'english',3,'Literature','What is "onomatopoeia"?','A word that sounds like what it describes','A type of metaphor','A story structure','A grammar rule',1);
ins($con,'english',3,'Literature','What is the "climax" of a story?','The beginning','The turning point or most exciting part','The ending','The introduction',2);
ins($con,'english',3,'Literature','What is "conflict" in a story?','The setting','The struggle between opposing forces','The theme','The narrator',2);
ins($con,'english',3,'Literature','What is a "myth"?','A true historical account','A traditional story explaining natural phenomena','A modern novel','A short poem',2);
// Level 4
ins($con,'english',4,'Literature','What is "symbolism"?','Using symbols to represent ideas','A type of rhyme','A grammar rule','A story structure',1);
ins($con,'english',4,'Literature','What is "flashback"?','A preview of future events','A return to earlier events in the narrative','The climax','The resolution',2);
ins($con,'english',4,'Literature','What is "satire"?','A serious drama','Using humor to criticize society','A love story','A biography',2);
ins($con,'english',4,'Literature','What is "tragedy" in literature?','A comedy','A story ending in disaster or death','A fairy tale','A myth',2);
ins($con,'english',4,'Literature','What is "comedy" in literature?','A sad story','A story with a happy ending and humor','A tragedy','A myth',2);
// Level 5
ins($con,'english',5,'Literature','What is "epic" poetry?','A short lyric poem','A long narrative poem about heroic deeds','A 14-line poem','A haiku',2);
ins($con,'english',5,'Literature','What is "soliloquy"?','A conversation between two characters','A speech by a character alone on stage','A type of poem','A narrative technique',2);
ins($con,'english',5,'Literature','What is "hubris"?','Excessive pride leading to downfall','A type of conflict','A narrative technique','A figure of speech',1);
ins($con,'english',5,'Literature','What is "catharsis"?','A plot twist','Emotional purging experienced by the audience','A type of irony','A narrative structure',2);
ins($con,'english',5,'Literature','What is "deus ex machina"?','A natural resolution','An unlikely plot device that resolves a problem','A type of character','A setting technique',2);
// Level 6
ins($con,'english',6,'Literature','What is "magical realism"?','Pure fantasy','Realistic narrative with magical elements','A type of poem','A historical novel',2);
ins($con,'english',6,'Literature','What is "dystopia"?','An ideal society','An imagined oppressive society','A historical setting','A type of poem',2);
ins($con,'english',6,'Literature','What is "bildungsroman"?','A mystery novel','A coming-of-age story','A war novel','A love story',2);
ins($con,'english',6,'Literature','What is "picaresque"?','A serious drama','A novel featuring a roguish hero in episodic adventures','A type of poem','A historical novel',2);
ins($con,'english',6,'Literature','What is "gothic literature"?','Light-hearted fiction','Fiction featuring dark, mysterious, supernatural elements','A type of comedy','A historical novel',2);
// Level 7
ins($con,'english',7,'Literature','What is "modernism" in literature?','Traditional storytelling','Experimental techniques rejecting traditional forms','A type of poem','A historical style',2);
ins($con,'english',7,'Literature','What is "postmodernism"?','A return to tradition','Self-referential, fragmented narrative style','A type of realism','A historical style',2);
ins($con,'english',7,'Literature','What is "existentialism" in literature?','Focus on society','Focus on individual freedom and responsibility','Focus on nature','Focus on history',2);
ins($con,'english',7,'Literature','What is "naturalism" in literature?','Idealized portrayal of life','Deterministic view showing humans controlled by environment','A type of fantasy','A historical style',2);
ins($con,'english',7,'Literature','What is "stream of consciousness" technique?','Dialogue-heavy writing','Continuous flow of a character\'s thoughts','Third-person narration','Epistolary form',2);
// Level 8
ins($con,'english',8,'Literature','What is "metafiction"?','A type of biography','Fiction that self-consciously addresses its own fictional nature','A historical novel','A type of poem',2);
ins($con,'english',8,'Literature','What is "intertextuality"?','Writing about nature','Relationship and references between texts','A grammar concept','A type of irony',2);
ins($con,'english',8,'Literature','What is "unreliable narrator"?','A truthful narrator','A narrator whose credibility is questionable','A third-person narrator','An omniscient narrator',2);
ins($con,'english',8,'Literature','What is "magical realism" associated with?','British literature','Latin American literature','American literature','French literature',2);
ins($con,'english',8,'Literature','What is "absurdism" in literature?','Belief in meaning','Belief that life is meaningless and irrational','A type of comedy','A historical style',2);
// Level 9
ins($con,'english',9,'Literature','What is "deconstruction" as a literary theory?','Building a story','Analyzing and questioning binary oppositions in texts','Summarizing a text','Finding the main idea',2);
ins($con,'english',9,'Literature','What is "New Criticism"?','A modern writing style','Close reading focusing on the text itself','A historical approach','A type of biography',2);
ins($con,'english',9,'Literature','What is "feminist literary criticism"?','Ignoring gender','Analyzing literature through the lens of gender and power','A type of biography','A historical approach',2);
ins($con,'english',9,'Literature','What is "postcolonial literature"?','Literature from colonial powers','Literature addressing effects of colonialism','A type of fantasy','A historical style',2);
ins($con,'english',9,'Literature','What is "ecocriticism"?','Study of economics in literature','Study of nature and environment in literature','A type of biography','A grammar theory',2);
// Level 10
ins($con,'english',10,'Literature','What is "hermeneutics" in literary study?','Grammar analysis','Theory and methodology of interpretation','Vocabulary study','Punctuation rules',2);
ins($con,'english',10,'Literature','What is "phenomenology" in literary theory?','Study of grammar','Study of conscious experience and perception','Study of vocabulary','Study of punctuation',2);
ins($con,'english',10,'Literature','What is "dialogism" (Bakhtin)?','Single authoritative voice','Multiple voices and perspectives in a text','A type of grammar','A narrative technique',2);
ins($con,'english',10,'Literature','What is "the death of the author" (Barthes)?','Authors are important','The author\'s intentions are irrelevant to meaning','A type of irony','A narrative technique',2);
ins($con,'english',10,'Literature','What is "mimesis"?','Exaggeration','Imitation or representation of reality in art','A type of irony','A narrative technique',2);

// ============================================================
// ENGLISH — Writing Skills
// ============================================================
// Level 1
ins($con,'english',1,'Writing Skills','What is a "sentence"?','A single word','A group of words expressing a complete thought','A paragraph','A letter',2);
ins($con,'english',1,'Writing Skills','What does a sentence start with?','A lowercase letter','A capital letter','A number','A punctuation mark',2);
ins($con,'english',1,'Writing Skills','What punctuation ends a question?','Period','Exclamation mark','Question mark','Comma',3);
ins($con,'english',1,'Writing Skills','What is a "paragraph"?','A single sentence','A group of related sentences','A single word','A letter',2);
ins($con,'english',1,'Writing Skills','What is the purpose of a "topic sentence"?','To end a paragraph','To introduce the main idea of a paragraph','To add details','To conclude',2);
// Level 2
ins($con,'english',2,'Writing Skills','What is "brainstorming"?','Writing a final draft','Generating ideas before writing','Editing a draft','Proofreading',2);
ins($con,'english',2,'Writing Skills','What is a "draft"?','A final version','A preliminary version of a piece of writing','A grammar rule','A punctuation mark',2);
ins($con,'english',2,'Writing Skills','What is "editing"?','Generating ideas','Checking and correcting a draft','Writing a first draft','Brainstorming',2);
ins($con,'english',2,'Writing Skills','What is a "concluding sentence"?','The first sentence','The sentence that wraps up a paragraph','A topic sentence','A supporting detail',2);
ins($con,'english',2,'Writing Skills','What is "proofreading"?','Writing a draft','Checking for spelling and grammar errors','Brainstorming','Outlining',2);
// Level 3
ins($con,'english',3,'Writing Skills','What is a "thesis statement"?','A supporting detail','The main argument of an essay','A conclusion','An introduction hook',2);
ins($con,'english',3,'Writing Skills','What is "coherence" in writing?','Using many words','Ideas flowing logically and clearly','Using complex sentences','Using many paragraphs',2);
ins($con,'english',3,'Writing Skills','What is a "transition word"?','A noun','A word that connects ideas (e.g., however, therefore)','A verb','An adjective',2);
ins($con,'english',3,'Writing Skills','What is "descriptive writing"?','Writing that argues','Writing that describes using sensory details','Writing that tells a story','Writing that informs',2);
ins($con,'english',3,'Writing Skills','What is "narrative writing"?','Writing that argues','Writing that tells a story','Writing that describes','Writing that informs',2);
// Level 4
ins($con,'english',4,'Writing Skills','What is "persuasive writing"?','Writing that describes','Writing that aims to convince the reader','Writing that tells a story','Writing that informs',2);
ins($con,'english',4,'Writing Skills','What is "expository writing"?','Writing that argues','Writing that explains or informs','Writing that tells a story','Writing that describes',2);
ins($con,'english',4,'Writing Skills','What is an "outline"?','A final draft','A plan organizing ideas before writing','A grammar rule','A punctuation guide',2);
ins($con,'english',4,'Writing Skills','What is "voice" in writing?','The volume','The writer\'s unique style and personality','The grammar','The punctuation',2);
ins($con,'english',4,'Writing Skills','What is "audience" in writing?','The writer','The intended readers','The topic','The purpose',2);
// Level 5
ins($con,'english',5,'Writing Skills','What is "diction"?','Sentence structure','Word choice','Punctuation','Grammar',2);
ins($con,'english',5,'Writing Skills','What is "syntax" in writing?','Word choice','Sentence structure and arrangement','Punctuation','Grammar rules',2);
ins($con,'english',5,'Writing Skills','What is "conciseness" in writing?','Using many words','Expressing ideas clearly with few words','Using complex sentences','Using many paragraphs',2);
ins($con,'english',5,'Writing Skills','What is "active voice"?','Subject receives the action','Subject performs the action','A type of tense','A grammar rule',2);
ins($con,'english',5,'Writing Skills','What is "passive voice"?','Subject performs the action','Subject receives the action','A type of tense','A grammar rule',2);
// Level 6
ins($con,'english',6,'Writing Skills','What is "ethos" in persuasive writing?','Emotional appeal','Appeal to credibility or ethics','Logical appeal','A type of evidence',2);
ins($con,'english',6,'Writing Skills','What is "pathos"?','Appeal to credibility','Emotional appeal','Logical appeal','A type of evidence',2);
ins($con,'english',6,'Writing Skills','What is "logos"?','Emotional appeal','Appeal to credibility','Logical appeal using facts and reasoning','A type of evidence',3);
ins($con,'english',6,'Writing Skills','What is a "counterargument"?','Supporting your own argument','Acknowledging and addressing opposing views','A type of evidence','A conclusion',2);
ins($con,'english',6,'Writing Skills','What is "citation"?','A type of sentence','Crediting sources used in writing','A grammar rule','A punctuation mark',2);
// Level 7
ins($con,'english',7,'Writing Skills','What is "anaphora" as a writing technique?','Repetition at the end of sentences','Repetition at the beginning of successive sentences','A type of metaphor','A grammar rule',2);
ins($con,'english',7,'Writing Skills','What is "chiasmus"?','Repetition of words','Reversal of grammatical structures in successive phrases','A type of metaphor','A grammar rule',2);
ins($con,'english',7,'Writing Skills','What is "epistrophe"?','Repetition at the beginning','Repetition at the end of successive clauses','A type of metaphor','A grammar rule',2);
ins($con,'english',7,'Writing Skills','What is "zeugma"?','A type of metaphor','Using one word to modify two others in different ways','A grammar rule','A punctuation technique',2);
ins($con,'english',7,'Writing Skills','What is "asyndeton"?','Using many conjunctions','Omitting conjunctions between clauses','A type of metaphor','A grammar rule',2);
// Level 8
ins($con,'english',8,'Writing Skills','What is "polysyndeton"?','Omitting conjunctions','Using many conjunctions in succession','A type of metaphor','A grammar rule',2);
ins($con,'english',8,'Writing Skills','What is "litotes"?','Exaggeration','Understatement using negation','A type of metaphor','A grammar rule',2);
ins($con,'english',8,'Writing Skills','What is "antithesis"?','Similarity of ideas','Juxtaposition of contrasting ideas','A type of metaphor','A grammar rule',2);
ins($con,'english',8,'Writing Skills','What is "periodic sentence"?','Main clause first','Main clause at the end after subordinate clauses','A simple sentence','A compound sentence',2);
ins($con,'english',8,'Writing Skills','What is "cumulative sentence"?','Main clause at the end','Main clause first, followed by modifying phrases','A simple sentence','A compound sentence',2);
// Level 9
ins($con,'english',9,'Writing Skills','What is "ekphrasis"?','A type of grammar','Vivid description of a visual work of art','A narrative technique','A type of poem',2);
ins($con,'english',9,'Writing Skills','What is "apophasis"?','Stating something directly','Mentioning something by saying you will not mention it','A type of metaphor','A grammar rule',2);
ins($con,'english',9,'Writing Skills','What is "amplification"?','Simplifying ideas','Expanding on an idea for emphasis','A type of metaphor','A grammar rule',2);
ins($con,'english',9,'Writing Skills','What is "anadiplosis"?','Repetition at the beginning','Repetition of the last word of one clause at the start of the next','A type of metaphor','A grammar rule',2);
ins($con,'english',9,'Writing Skills','What is "syllepsis"?','A type of grammar','Using a word to apply to two others in different senses','A narrative technique','A punctuation rule',2);
// Level 10
ins($con,'english',10,'Writing Skills','What is "enargeia"?','A grammar rule','Vivid, detailed description that brings a scene to life','A type of irony','A narrative technique',2);
ins($con,'english',10,'Writing Skills','What is "auxesis"?','Decreasing emphasis','Arrangement of words in order of increasing importance','A type of metaphor','A grammar rule',2);
ins($con,'english',10,'Writing Skills','What is "meiosis"?','Exaggeration','Deliberate understatement to diminish importance','A type of metaphor','A grammar rule',2);
ins($con,'english',10,'Writing Skills','What is "prosopopoeia"?','A grammar rule','Giving voice to an absent or imaginary person','A type of irony','A narrative technique',2);
ins($con,'english',10,'Writing Skills','What is "hypotyposis"?','A grammar rule','Vivid description of a scene or event','A type of irony','A narrative technique',2);

// ============================================================
// MATH — Algebra
// ============================================================
// Level 1
ins($con,'math',1,'Algebra','What is 2 + 3?','4','5','6','7',2);
ins($con,'math',1,'Algebra','What is x if x + 2 = 5?','2','3','4','5',2);
ins($con,'math',1,'Algebra','What is 10 - 4?','5','6','7','8',2);
ins($con,'math',1,'Algebra','What is 3 × 4?','10','11','12','13',3);
ins($con,'math',1,'Algebra','What is 15 ÷ 3?','4','5','6','7',2);
// Level 2
ins($con,'math',2,'Algebra','Solve: 2x = 10','x = 3','x = 4','x = 5','x = 6',3);
ins($con,'math',2,'Algebra','What is the value of 3x + 2 when x = 2?','6','7','8','9',3);
ins($con,'math',2,'Algebra','Simplify: 4x + 3x','7x','7x²','12x','x',1);
ins($con,'math',2,'Algebra','Solve: x - 5 = 3','x = 7','x = 8','x = 9','x = 10',2);
ins($con,'math',2,'Algebra','What is 2² + 3?','6','7','8','9',2);
// Level 3
ins($con,'math',3,'Algebra','Solve: 3x + 6 = 15','x = 2','x = 3','x = 4','x = 5',2);
ins($con,'math',3,'Algebra','What is the slope of y = 2x + 3?','3','2','1','0',2);
ins($con,'math',3,'Algebra','Simplify: (2x)(3x)','5x','6x','5x²','6x²',4);
ins($con,'math',3,'Algebra','Solve: 2x - 4 = 8','x = 4','x = 5','x = 6','x = 7',3);
ins($con,'math',3,'Algebra','What is the y-intercept of y = 3x - 2?','-2','2','3','-3',1);
// Level 4
ins($con,'math',4,'Algebra','Solve: x² = 16','x = 2','x = 4','x = 8','x = 16',2);
ins($con,'math',4,'Algebra','Factor: x² + 5x + 6','(x+2)(x+3)','(x+1)(x+6)','(x+2)(x+4)','(x+3)(x+3)',1);
ins($con,'math',4,'Algebra','Solve: 2x + 3y = 12, x = 3','y = 2','y = 3','y = 4','y = 5',1);
ins($con,'math',4,'Algebra','What is the degree of 3x² + 2x + 1?','1','2','3','4',2);
ins($con,'math',4,'Algebra','Simplify: (x + 2)²','x² + 4','x² + 4x + 4','x² + 2x + 4','x² + 4x',2);
// Level 5
ins($con,'math',5,'Algebra','Solve: x² - 5x + 6 = 0','x = 2 or x = 3','x = 1 or x = 6','x = -2 or x = -3','x = 2 or x = -3',1);
ins($con,'math',5,'Algebra','What is the discriminant of x² + 2x + 1?','0','1','2','4',1);
ins($con,'math',5,'Algebra','Solve: |x - 3| = 5','x = 8 or x = -2','x = 8 or x = 2','x = -8 or x = 2','x = 8 or x = -8',1);
ins($con,'math',5,'Algebra','Simplify: (2x³)(3x²)','5x⁵','6x⁵','6x⁶','5x⁶',2);
ins($con,'math',5,'Algebra','What is the sum of roots of x² - 5x + 6 = 0?','5','6','-5','-6',1);
// Level 6
ins($con,'math',6,'Algebra','Solve: 2x² - 8 = 0','x = ±2','x = ±4','x = ±8','x = ±1',1);
ins($con,'math',6,'Algebra','What is the vertex of y = (x - 2)² + 3?','(2, 3)','(-2, 3)','(2, -3)','(-2, -3)',1);
ins($con,'math',6,'Algebra','Simplify: (x² - 4)/(x - 2)','x + 2','x - 2','x² + 2','x + 4',1);
ins($con,'math',6,'Algebra','Solve: log₂(x) = 3','x = 6','x = 8','x = 9','x = 12',2);
ins($con,'math',6,'Algebra','What is the product of roots of 2x² - 6x + 4 = 0?','3','2','1','4',2);
// Level 7
ins($con,'math',7,'Algebra','Solve: 2^x = 32','x = 4','x = 5','x = 6','x = 7',2);
ins($con,'math',7,'Algebra','What is the inverse of f(x) = 2x + 3?','f⁻¹(x) = (x-3)/2','f⁻¹(x) = (x+3)/2','f⁻¹(x) = 2x - 3','f⁻¹(x) = x/2 + 3',1);
ins($con,'math',7,'Algebra','Simplify: (x³ - 8)/(x - 2)','x² + 2x + 4','x² - 2x + 4','x² + 4','x² - 4',1);
ins($con,'math',7,'Algebra','Solve: e^x = 1','x = 0','x = 1','x = e','x = -1',1);
ins($con,'math',7,'Algebra','What is the range of f(x) = x²?','All real numbers','x ≥ 0','x ≤ 0','x > 0',2);
// Level 8
ins($con,'math',8,'Algebra','Solve: x⁴ - 5x² + 4 = 0','x = ±1, ±2','x = ±1, ±4','x = ±2, ±4','x = ±1, ±3',1);
ins($con,'math',8,'Algebra','What is the remainder when x³ - 2x + 1 is divided by (x - 1)?','0','1','2','3',1);
ins($con,'math',8,'Algebra','Solve: log(x) + log(x-3) = 1','x = 5','x = 4','x = 3','x = 2',1);
ins($con,'math',8,'Algebra','What is the sum of an arithmetic series with a₁=2, d=3, n=10?','155','165','175','185',2);
ins($con,'math',8,'Algebra','Simplify: (2 + 3i)(2 - 3i)','4 - 9i','13','4 + 9i','13i',2);
// Level 9
ins($con,'math',9,'Algebra','What is the sum of an infinite geometric series with a=3, r=1/2?','5','6','7','8',2);
ins($con,'math',9,'Algebra','Solve: x^(2/3) = 4','x = 8','x = 6','x = 4','x = 2',1);
ins($con,'math',9,'Algebra','What is the binomial expansion of (x + y)³?','x³ + 3x²y + 3xy² + y³','x³ + y³','x³ + 3xy + y³','x³ + 2x²y + 2xy² + y³',1);
ins($con,'math',9,'Algebra','Solve: |2x - 3| < 5','-1 < x < 4','x < -1 or x > 4','-1 ≤ x ≤ 4','x ≤ -1 or x ≥ 4',1);
ins($con,'math',9,'Algebra','What is the partial fraction of (2x+1)/((x+1)(x-1))?','1/(x+1) + 1/(x-1)','3/(2(x+1)) + 1/(2(x-1))','1/(x-1) - 1/(x+1)','2/(x+1) - 1/(x-1)',2);
// Level 10
ins($con,'math',10,'Algebra','What is the number of real roots of x⁵ - x + 1 = 0?','1','2','3','5',1);
ins($con,'math',10,'Algebra','Solve: 2^(2x) - 5·2^x + 4 = 0','x = 0 or x = 2','x = 1 or x = 2','x = 0 or x = 1','x = -1 or x = 2',3);
ins($con,'math',10,'Algebra','What is the coefficient of x³ in (2x - 1)⁵?','-40','40','-80','80',3);
ins($con,'math',10,'Algebra','Solve: log₃(x+1) + log₃(x-1) = 2','x = √10','x = √8','x = 3','x = 4',1);
ins($con,'math',10,'Algebra','What is the sum of all roots of x⁴ - 2x³ - x + 2 = 0?','2','1','3','4',1);

// ============================================================
// MATH — Geometry
// ============================================================
// Level 1
ins($con,'math',1,'Geometry','How many sides does a triangle have?','2','3','4','5',2);
ins($con,'math',1,'Geometry','What shape has 4 equal sides?','Rectangle','Triangle','Square','Circle',3);
ins($con,'math',1,'Geometry','What is the perimeter of a square with side 3?','9','12','6','15',2);
ins($con,'math',1,'Geometry','How many degrees are in a right angle?','45','60','90','180',3);
ins($con,'math',1,'Geometry','What is the area of a rectangle 4×3?','7','10','12','14',3);
// Level 2
ins($con,'math',2,'Geometry','What is the area of a triangle with base 6 and height 4?','10','12','14','24',2);
ins($con,'math',2,'Geometry','What is the circumference of a circle with radius 7? (π≈3.14)','21.98','43.96','44','22',2);
ins($con,'math',2,'Geometry','How many degrees are in a straight angle?','90','120','180','360',3);
ins($con,'math',2,'Geometry','What is the sum of angles in a triangle?','90°','180°','270°','360°',2);
ins($con,'math',2,'Geometry','What is the area of a circle with radius 5? (π≈3.14)','78.5','31.4','15.7','157',1);
// Level 3
ins($con,'math',3,'Geometry','What is the Pythagorean theorem?','a + b = c','a² + b² = c²','a² - b² = c²','a × b = c',2);
ins($con,'math',3,'Geometry','Find the hypotenuse of a right triangle with legs 3 and 4.','5','6','7','8',1);
ins($con,'math',3,'Geometry','What is the volume of a cube with side 3?','9','18','27','36',3);
ins($con,'math',3,'Geometry','What is the sum of interior angles of a quadrilateral?','180°','270°','360°','540°',3);
ins($con,'math',3,'Geometry','What is the area of a parallelogram with base 8 and height 5?','13','30','40','45',3);
// Level 4
ins($con,'math',4,'Geometry','What is the volume of a cylinder with r=3, h=5? (π≈3.14)','141.3','94.2','47.1','188.4',1);
ins($con,'math',4,'Geometry','What is the surface area of a cube with side 4?','64','96','48','32',2);
ins($con,'math',4,'Geometry','What is the sum of interior angles of a pentagon?','360°','450°','540°','720°',3);
ins($con,'math',4,'Geometry','What is the diagonal of a square with side 5?','5√2','5√3','10','5',1);
ins($con,'math',4,'Geometry','What is the area of a trapezoid with bases 6 and 10, height 4?','32','40','48','16',1);
// Level 5
ins($con,'math',5,'Geometry','What is the volume of a cone with r=3, h=4? (π≈3.14)','37.68','75.36','12.56','113.04',1);
ins($con,'math',5,'Geometry','What is the equation of a circle with center (0,0) and radius 5?','x² + y² = 5','x² + y² = 25','x + y = 5','x² - y² = 25',2);
ins($con,'math',5,'Geometry','What is the volume of a sphere with r=3? (π≈3.14)','113.04','75.36','37.68','150.72',1);
ins($con,'math',5,'Geometry','What is the sum of interior angles of a hexagon?','540°','720°','900°','360°',2);
ins($con,'math',5,'Geometry','Two parallel lines are cut by a transversal. Alternate interior angles are:','Supplementary','Complementary','Equal','Different',3);
// Level 6
ins($con,'math',6,'Geometry','What is the distance between (1,2) and (4,6)?','5','4','3','6',1);
ins($con,'math',6,'Geometry','What is the midpoint of (2,4) and (6,8)?','(4,6)','(3,5)','(4,5)','(3,6)',1);
ins($con,'math',6,'Geometry','What is the slope of a line through (1,2) and (3,6)?','1','2','3','4',2);
ins($con,'math',6,'Geometry','What is the area of a regular hexagon with side 4?','24√3','16√3','8√3','32√3',1);
ins($con,'math',6,'Geometry','What is the equation of a line with slope 2 and y-intercept 3?','y = 3x + 2','y = 2x + 3','y = 2x - 3','y = 3x - 2',2);
// Level 7
ins($con,'math',7,'Geometry','What is the angle inscribed in a semicircle?','45°','60°','90°','180°',3);
ins($con,'math',7,'Geometry','What is the length of an arc with r=6 and central angle 60°? (π≈3.14)','6.28','12.56','3.14','9.42',1);
ins($con,'math',7,'Geometry','What is the area of a sector with r=4 and central angle 90°? (π≈3.14)','12.56','6.28','25.12','3.14',1);
ins($con,'math',7,'Geometry','What is the locus of points equidistant from two points?','A circle','The perpendicular bisector','A line','A parabola',2);
ins($con,'math',7,'Geometry','What is the equation of a parabola with vertex (0,0) opening upward?','y = x²','x = y²','y = -x²','x = -y²',1);
// Level 8
ins($con,'math',8,'Geometry','What is the equation of an ellipse with a=5, b=3, centered at origin?','x²/25 + y²/9 = 1','x²/9 + y²/25 = 1','x²/5 + y²/3 = 1','x²/3 + y²/5 = 1',1);
ins($con,'math',8,'Geometry','What is the eccentricity of a circle?','0','1','0.5','2',1);
ins($con,'math',8,'Geometry','What is the equation of a hyperbola with a=3, b=4, centered at origin?','x²/9 - y²/16 = 1','x²/16 - y²/9 = 1','x²/9 + y²/16 = 1','x²/3 - y²/4 = 1',1);
ins($con,'math',8,'Geometry','What is the area of a triangle with vertices (0,0), (4,0), (0,3)?','6','12','7','5',1);
ins($con,'math',8,'Geometry','What is the volume of a frustum with r₁=3, r₂=1, h=4? (π≈3.14)','58.64','29.32','87.96','44.18',1);
// Level 9
ins($con,'math',9,'Geometry','What is the formula for the area of a triangle using the cross product?','|a × b|/2','|a · b|/2','|a + b|/2','|a - b|/2',1);
ins($con,'math',9,'Geometry','What is the Euler line in a triangle?','The altitude','The line through centroid, circumcenter, and orthocenter','The angle bisector','The perpendicular bisector',2);
ins($con,'math',9,'Geometry','What is the nine-point circle?','The circumscribed circle','A circle passing through 9 special points of a triangle','The inscribed circle','A circle through the vertices',2);
ins($con,'math',9,'Geometry','What is the formula for the circumradius of a triangle?','R = abc/4K','R = abc/2K','R = 2K/abc','R = K/abc',1);
ins($con,'math',9,'Geometry','What is the Gauss-Bonnet theorem about?','Area of circles','Relationship between curvature and topology','Volume of spheres','Angles in triangles',2);
// Level 10
ins($con,'math',10,'Geometry','What is the Poincaré disk model?','A model of Euclidean geometry','A model of hyperbolic geometry','A model of elliptic geometry','A model of projective geometry',2);
ins($con,'math',10,'Geometry','What is the Desargues theorem about?','Circles','Two triangles in perspective from a point','Parallel lines','Perpendicular bisectors',2);
ins($con,'math',10,'Geometry','What is the Cayley-Menger determinant used for?','Finding angles','Determining distances in a simplex','Finding areas','Determining volumes of spheres',2);
ins($con,'math',10,'Geometry','What is the Minkowski metric used in?','Euclidean geometry','Special relativity spacetime geometry','Hyperbolic geometry','Projective geometry',2);
ins($con,'math',10,'Geometry','What is the Fano plane?','A type of circle','The smallest projective plane','A type of polygon','A type of polyhedron',2);

// ============================================================
// MATH — Statistics
// ============================================================
// Level 1
ins($con,'math',1,'Statistics','What is the mean of 2, 4, 6?','3','4','5','6',2);
ins($con,'math',1,'Statistics','What is the median of 1, 3, 5?','1','3','5','4',2);
ins($con,'math',1,'Statistics','What is the mode of 2, 2, 3, 4?','2','3','4','1',1);
ins($con,'math',1,'Statistics','What is the range of 3, 7, 10?','3','7','10','7',2);
ins($con,'math',1,'Statistics','How many data points are in: 5, 8, 12, 15?','3','4','5','6',2);
// Level 2
ins($con,'math',2,'Statistics','What is the mean of 10, 20, 30, 40?','20','25','30','35',2);
ins($con,'math',2,'Statistics','What is the median of 4, 7, 9, 11?','7','8','9','10',2);
ins($con,'math',2,'Statistics','What is the mode of 1, 2, 2, 3, 3, 3?','1','2','3','4',3);
ins($con,'math',2,'Statistics','What is the range of 5, 12, 18, 25?','13','20','25','30',2);
ins($con,'math',2,'Statistics','What type of graph uses bars to show data?','Pie chart','Line graph','Bar graph','Scatter plot',3);
// Level 3
ins($con,'math',3,'Statistics','What is the variance of 2, 4, 6? (mean=4)','2','4','8','3',2);
ins($con,'math',3,'Statistics','What is the standard deviation if variance = 9?','3','9','81','4.5',1);
ins($con,'math',3,'Statistics','What does a frequency table show?','Mean values','How often each value occurs','The range','The median',2);
ins($con,'math',3,'Statistics','What is a "class interval" in grouped data?','The mean','The range of values in a group','The mode','The median',2);
ins($con,'math',3,'Statistics','What is the cumulative frequency?','The mode','Running total of frequencies','The mean','The range',2);
// Level 4
ins($con,'math',4,'Statistics','What is the interquartile range (IQR)?','Q3 - Q1','Q2 - Q1','Q3 - Q2','Q4 - Q1',1);
ins($con,'math',4,'Statistics','What is Q2 also known as?','Mean','Mode','Median','Range',3);
ins($con,'math',4,'Statistics','What is a "box plot" used for?','Showing frequency','Showing distribution and quartiles','Showing correlation','Showing probability',2);
ins($con,'math',4,'Statistics','What is an "outlier"?','The mean','A data point far from others','The mode','The median',2);
ins($con,'math',4,'Statistics','What is the coefficient of variation?','Standard deviation / mean × 100%','Mean / standard deviation','Variance / mean','Range / mean',1);
// Level 5
ins($con,'math',5,'Statistics','What is a "normal distribution"?','A skewed distribution','A bell-shaped symmetric distribution','A uniform distribution','A bimodal distribution',2);
ins($con,'math',5,'Statistics','What percentage of data falls within 1 standard deviation in a normal distribution?','50%','68%','95%','99.7%',2);
ins($con,'math',5,'Statistics','What is a "z-score"?','The mean','Number of standard deviations from the mean','The variance','The range',2);
ins($con,'math',5,'Statistics','What is "correlation"?','Causation','Relationship between two variables','The mean','The variance',2);
ins($con,'math',5,'Statistics','What does r = 1 indicate in correlation?','No correlation','Perfect negative correlation','Perfect positive correlation','Weak correlation',3);
// Level 6
ins($con,'math',6,'Statistics','What is "regression analysis"?','Finding the mean','Modeling relationship between variables','Finding the mode','Finding the range',2);
ins($con,'math',6,'Statistics','What is the least squares method?','Finding the median','Minimizing sum of squared residuals','Finding the mode','Finding the range',2);
ins($con,'math',6,'Statistics','What is a "confidence interval"?','A single estimate','A range likely containing the true parameter','The standard deviation','The variance',2);
ins($con,'math',6,'Statistics','What is "sampling bias"?','Random sampling','Systematic error in sample selection','The standard error','The confidence level',2);
ins($con,'math',6,'Statistics','What is the Central Limit Theorem?','The mean equals the median','Sample means approach normal distribution as n increases','The variance equals the standard deviation','The mode equals the mean',2);
// Level 7
ins($con,'math',7,'Statistics','What is a "hypothesis test"?','Finding the mean','Statistical procedure to test a claim','Finding the mode','Finding the range',2);
ins($con,'math',7,'Statistics','What is a "p-value"?','The mean','Probability of results given null hypothesis is true','The variance','The confidence level',2);
ins($con,'math',7,'Statistics','What is "Type I error"?','Accepting a false null hypothesis','Rejecting a true null hypothesis','Accepting a true null hypothesis','Rejecting a false null hypothesis',2);
ins($con,'math',7,'Statistics','What is "Type II error"?','Rejecting a true null hypothesis','Accepting a false null hypothesis','Accepting a true null hypothesis','Rejecting a false null hypothesis',2);
ins($con,'math',7,'Statistics','What is the chi-square test used for?','Testing means','Testing independence of categorical variables','Testing variances','Testing correlations',2);
// Level 8
ins($con,'math',8,'Statistics','What is ANOVA?','Analysis of one variable','Analysis of variance between multiple groups','A type of regression','A type of correlation',2);
ins($con,'math',8,'Statistics','What is "multicollinearity"?','High correlation between independent variables','High correlation between dependent variables','A type of bias','A type of error',1);
ins($con,'math',8,'Statistics','What is the F-statistic?','Ratio of means','Ratio of variances between and within groups','Ratio of standard deviations','Ratio of medians',2);
ins($con,'math',8,'Statistics','What is "Bayesian statistics"?','Frequentist approach','Updating probability based on prior knowledge','A type of regression','A type of correlation',2);
ins($con,'math',8,'Statistics','What is "bootstrapping" in statistics?','A type of regression','Resampling with replacement to estimate statistics','A type of correlation','A type of bias',2);
// Level 9
ins($con,'math',9,'Statistics','What is the Kolmogorov-Smirnov test?','Testing means','Testing if data follows a distribution','Testing variances','Testing correlations',2);
ins($con,'math',9,'Statistics','What is "principal component analysis" (PCA)?','A type of regression','Dimensionality reduction technique','A type of correlation','A type of hypothesis test',2);
ins($con,'math',9,'Statistics','What is "maximum likelihood estimation"?','Finding the mean','Finding parameters that maximize the likelihood of observed data','Finding the mode','Finding the range',2);
ins($con,'math',9,'Statistics','What is the "expectation-maximization" algorithm?','A type of regression','Iterative method for finding maximum likelihood estimates with missing data','A type of correlation','A type of hypothesis test',2);
ins($con,'math',9,'Statistics','What is "Markov Chain Monte Carlo"?','A type of regression','Sampling method using Markov chains','A type of correlation','A type of hypothesis test',2);
// Level 10
ins($con,'math',10,'Statistics','What is the Cramér-Rao bound?','The maximum variance','Lower bound on variance of unbiased estimators','The minimum mean','The maximum standard deviation',2);
ins($con,'math',10,'Statistics','What is "sufficient statistic"?','A statistic that is always sufficient','A statistic that captures all information about a parameter','A statistic equal to the mean','A statistic equal to the variance',2);
ins($con,'math',10,'Statistics','What is the Neyman-Pearson lemma?','About confidence intervals','Optimal test for simple hypotheses using likelihood ratio','About regression','About correlation',2);
ins($con,'math',10,'Statistics','What is "exponential family" of distributions?','Distributions with exponential tails','Distributions with a specific mathematical form','Distributions with no variance','Distributions with infinite mean',2);
ins($con,'math',10,'Statistics','What is "Fisher information"?','A type of regression','Measure of information a sample carries about a parameter','A type of correlation','A type of hypothesis test',2);

// ============================================================
// MATH — Probability
// ============================================================
// Level 1
ins($con,'math',1,'Probability','What is the probability of flipping heads on a fair coin?','1/4','1/3','1/2','1',3);
ins($con,'math',1,'Probability','A bag has 3 red and 2 blue balls. What is P(red)?','2/5','3/5','1/2','1/5',2);
ins($con,'math',1,'Probability','What is the probability of rolling a 3 on a fair die?','1/3','1/4','1/6','1/2',3);
ins($con,'math',1,'Probability','What is the probability of an impossible event?','1','0.5','0','2',3);
ins($con,'math',1,'Probability','What is the probability of a certain event?','0','0.5','1','2',3);
// Level 2
ins($con,'math',2,'Probability','What is P(A or B) if A and B are mutually exclusive?','P(A) × P(B)','P(A) + P(B)','P(A) - P(B)','P(A) / P(B)',2);
ins($con,'math',2,'Probability','What is P(A and B) if A and B are independent?','P(A) + P(B)','P(A) - P(B)','P(A) × P(B)','P(A) / P(B)',3);
ins($con,'math',2,'Probability','What is the complement of event A?','P(A)','1 - P(A)','P(A) + 1','P(A) - 1',2);
ins($con,'math',2,'Probability','A die is rolled. What is P(even)?','1/6','1/3','1/2','2/3',3);
ins($con,'math',2,'Probability','What is P(not 6) when rolling a die?','1/6','5/6','1/2','2/3',2);
// Level 3
ins($con,'math',3,'Probability','What is conditional probability P(A|B)?','P(A) × P(B)','P(A ∩ B) / P(B)','P(A) + P(B)','P(A) / P(B)',2);
ins($con,'math',3,'Probability','What is the expected value of rolling a fair die?','3','3.5','4','2.5',2);
ins($con,'math',3,'Probability','What is a "sample space"?','A single outcome','The set of all possible outcomes','The probability of an event','A type of event',2);
ins($con,'math',3,'Probability','What is a "tree diagram" used for?','Showing data distribution','Showing all possible outcomes of sequential events','Showing correlation','Showing regression',2);
ins($con,'math',3,'Probability','Two cards are drawn without replacement. Are the events independent?','Yes','No','Sometimes','Always',2);
// Level 4
ins($con,'math',4,'Probability','What is the binomial probability formula?','nCr × p^r × q^(n-r)','n! × p^r','nPr × p^r','p^n × q^r',1);
ins($con,'math',4,'Probability','What is nCr (combinations)?','n! / r!','n! / (r!(n-r)!)','n! / (n-r)!','r! / n!',2);
ins($con,'math',4,'Probability','What is the probability of getting exactly 2 heads in 3 coin flips?','1/4','3/8','1/2','1/8',2);
ins($con,'math',4,'Probability','What is the geometric distribution used for?','Number of successes','Number of trials until first success','Number of failures','Number of events',2);
ins($con,'math',4,'Probability','What is the Poisson distribution used for?','Continuous events','Number of events in a fixed interval','Binary outcomes','Normal events',2);
// Level 5
ins($con,'math',5,'Probability','What is the normal distribution characterized by?','Mean and range','Mean and standard deviation','Median and mode','Variance and range',2);
ins($con,'math',5,'Probability','What is the law of large numbers?','Sample mean equals population mean','Sample mean approaches population mean as n increases','Variance decreases with n','Standard deviation increases with n',2);
ins($con,'math',5,'Probability','What is Bayes\' theorem?','P(A|B) = P(A)P(B)','P(A|B) = P(B|A)P(A)/P(B)','P(A|B) = P(A)/P(B)','P(A|B) = P(A) + P(B)',2);
ins($con,'math',5,'Probability','What is a "random variable"?','A fixed value','A variable whose value is determined by a random process','A type of event','A type of sample space',2);
ins($con,'math',5,'Probability','What is the variance of a random variable X?','E[X]','E[X²] - (E[X])²','E[X] - E[X²]','(E[X])² - E[X²]',2);
// Level 6
ins($con,'math',6,'Probability','What is the moment generating function used for?','Finding the mean','Generating all moments of a distribution','Finding the variance','Finding the mode',2);
ins($con,'math',6,'Probability','What is the characteristic function of a distribution?','Fourier transform of the PDF','Laplace transform of the PDF','The CDF','The PMF',1);
ins($con,'math',6,'Probability','What is the joint probability distribution?','Probability of one event','Probability of two or more events simultaneously','Conditional probability','Marginal probability',2);
ins($con,'math',6,'Probability','What is the marginal distribution?','Joint distribution','Distribution of one variable ignoring others','Conditional distribution','Posterior distribution',2);
ins($con,'math',6,'Probability','What is the covariance of X and Y?','E[X]E[Y]','E[XY] - E[X]E[Y]','E[X+Y]','E[X-Y]',2);
// Level 7
ins($con,'math',7,'Probability','What is the Chebyshev inequality?','P(|X-μ| ≥ kσ) ≤ 1/k²','P(|X-μ| ≥ kσ) ≥ 1/k²','P(|X-μ| ≤ kσ) ≤ 1/k²','P(|X-μ| ≤ kσ) ≥ 1/k²',1);
ins($con,'math',7,'Probability','What is the central limit theorem?','Mean equals median','Sum of independent random variables approaches normal distribution','Variance equals standard deviation','Mode equals mean',2);
ins($con,'math',7,'Probability','What is a "martingale"?','A type of distribution','A stochastic process where future expected value equals current value','A type of random variable','A type of event',2);
ins($con,'math',7,'Probability','What is the Markov property?','Future depends on all past states','Future depends only on current state','Future is independent of all states','Future equals past',2);
ins($con,'math',7,'Probability','What is the renewal theory?','Study of new distributions','Study of recurrence times of events','Study of new random variables','Study of new sample spaces',2);
// Level 8
ins($con,'math',8,'Probability','What is the Poisson process?','A discrete distribution','A continuous-time process counting events at constant rate','A type of random walk','A type of martingale',2);
ins($con,'math',8,'Probability','What is the Brownian motion?','A discrete random walk','A continuous-time random process with independent increments','A type of Markov chain','A type of martingale',2);
ins($con,'math',8,'Probability','What is the Itô integral?','A type of Riemann integral','A stochastic integral with respect to Brownian motion','A type of Lebesgue integral','A type of Stieltjes integral',2);
ins($con,'math',8,'Probability','What is the Kolmogorov axioms?','Axioms of algebra','Axioms defining probability measure','Axioms of geometry','Axioms of statistics',2);
ins($con,'math',8,'Probability','What is the law of total probability?','P(A) = P(A|B)','P(A) = Σ P(A|Bᵢ)P(Bᵢ)','P(A) = P(A) + P(B)','P(A) = P(A) × P(B)',2);
// Level 9
ins($con,'math',9,'Probability','What is the Radon-Nikodym theorem?','About measures','Existence of density function between two measures','About probability','About statistics',2);
ins($con,'math',9,'Probability','What is the ergodic theorem?','About random walks','Time averages equal space averages for ergodic processes','About Markov chains','About martingales',2);
ins($con,'math',9,'Probability','What is the large deviation principle?','About small samples','About exponential decay of probabilities of rare events','About normal distributions','About Poisson processes',2);
ins($con,'math',9,'Probability','What is the Cramér theorem?','About means','About large deviations for sums of random variables','About variances','About correlations',2);
ins($con,'math',9,'Probability','What is the optional stopping theorem?','About stopping times','Expected value of martingale at stopping time equals initial value','About Markov chains','About Brownian motion',2);
// Level 10
ins($con,'math',10,'Probability','What is the Doob-Meyer decomposition?','About martingales','Decomposition of submartingale into martingale and predictable process','About Markov chains','About Brownian motion',2);
ins($con,'math',10,'Probability','What is the Girsanov theorem?','About measures','Change of measure for Brownian motion','About Markov chains','About martingales',2);
ins($con,'math',10,'Probability','What is the Feynman-Kac formula?','About algebra','Connection between PDEs and stochastic processes','About geometry','About statistics',2);
ins($con,'math',10,'Probability','What is the Lévy-Khintchine formula?','About normal distributions','Characteristic function of infinitely divisible distributions','About Poisson processes','About Brownian motion',2);
ins($con,'math',10,'Probability','What is the de Finetti theorem?','About independence','Exchangeable sequences are mixtures of i.i.d. sequences','About Markov chains','About martingales',2);

// ============================================================
// MATH — Functions & Equations
// ============================================================
// Level 1
ins($con,'math',1,'Functions & Equations','What is f(x) = 2x when x = 3?','5','6','7','8',2);
ins($con,'math',1,'Functions & Equations','What is the output of f(x) = x + 1 when x = 4?','4','5','6','7',2);
ins($con,'math',1,'Functions & Equations','Solve: x + 3 = 7','x = 3','x = 4','x = 5','x = 6',2);
ins($con,'math',1,'Functions & Equations','What is f(2) if f(x) = 3x?','5','6','7','8',2);
ins($con,'math',1,'Functions & Equations','Solve: 2x = 8','x = 2','x = 3','x = 4','x = 5',3);
// Level 2
ins($con,'math',2,'Functions & Equations','What is the domain of f(x) = 1/x?','All real numbers','All real numbers except 0','x > 0','x < 0',2);
ins($con,'math',2,'Functions & Equations','What is f(g(x)) called?','Sum','Difference','Composition','Product',3);
ins($con,'math',2,'Functions & Equations','Solve: 3x - 2 = 7','x = 2','x = 3','x = 4','x = 5',2);
ins($con,'math',2,'Functions & Equations','What is the range of f(x) = x²?','All real numbers','x ≥ 0','x ≤ 0','x > 0',2);
ins($con,'math',2,'Functions & Equations','What is f⁻¹(x) if f(x) = x + 3?','x - 3','x + 3','3 - x','3x',1);
// Level 3
ins($con,'math',3,'Functions & Equations','What is the vertical line test used for?','Finding the range','Determining if a graph is a function','Finding the domain','Finding the inverse',2);
ins($con,'math',3,'Functions & Equations','What is a "one-to-one" function?','Every output has exactly one input','Every input has exactly one output','A function with no inverse','A constant function',1);
ins($con,'math',3,'Functions & Equations','Solve: x² - 4 = 0','x = ±1','x = ±2','x = ±3','x = ±4',2);
ins($con,'math',3,'Functions & Equations','What is the horizontal line test used for?','Finding the range','Determining if a function is one-to-one','Finding the domain','Finding the inverse',2);
ins($con,'math',3,'Functions & Equations','What is f(x) = c called?','Linear function','Quadratic function','Constant function','Exponential function',3);
// Level 4
ins($con,'math',4,'Functions & Equations','What is the quadratic formula?','x = -b ± √(b²-4ac) / 2a','x = b ± √(b²-4ac) / 2a','x = -b ± √(b²+4ac) / 2a','x = -b ± √(b²-4ac) / a',1);
ins($con,'math',4,'Functions & Equations','What is the axis of symmetry of y = ax² + bx + c?','x = b/2a','x = -b/2a','x = c/a','x = -c/a',2);
ins($con,'math',4,'Functions & Equations','What is an "even function"?','f(-x) = f(x)','f(-x) = -f(x)','f(x) = f(x+1)','f(x) = -f(x)',1);
ins($con,'math',4,'Functions & Equations','What is an "odd function"?','f(-x) = f(x)','f(-x) = -f(x)','f(x) = f(x+1)','f(x) = -f(x)',2);
ins($con,'math',4,'Functions & Equations','What is the period of f(x) = sin(x)?','π','2π','π/2','4π',2);
// Level 5
ins($con,'math',5,'Functions & Equations','What is the amplitude of f(x) = 3sin(x)?','1','2','3','6',3);
ins($con,'math',5,'Functions & Equations','What is the inverse of f(x) = e^x?','f⁻¹(x) = ln(x)','f⁻¹(x) = log(x)','f⁻¹(x) = x²','f⁻¹(x) = 1/x',1);
ins($con,'math',5,'Functions & Equations','Solve: 2^x = 16','x = 3','x = 4','x = 5','x = 6',2);
ins($con,'math',5,'Functions & Equations','What is the domain of f(x) = √x?','All real numbers','x ≥ 0','x > 0','x ≤ 0',2);
ins($con,'math',5,'Functions & Equations','What is the asymptote of f(x) = 1/x?','y = 1','y = 0 and x = 0','y = x','x = 1',2);
// Level 6
ins($con,'math',6,'Functions & Equations','What is the derivative of f(x) = x²?','x','2x','x²','2',2);
ins($con,'math',6,'Functions & Equations','What is the integral of f(x) = 2x?','x','x²','2x²','x² + C',4);
ins($con,'math',6,'Functions & Equations','What is a "piecewise function"?','A function with one rule','A function defined by different rules for different intervals','A constant function','A linear function',2);
ins($con,'math',6,'Functions & Equations','What is the floor function ⌊3.7⌋?','3','4','3.7','3.5',1);
ins($con,'math',6,'Functions & Equations','What is the ceiling function ⌈3.2⌉?','3','4','3.2','3.5',2);
// Level 7
ins($con,'math',7,'Functions & Equations','What is the Fourier series used for?','Solving differential equations','Representing periodic functions as sum of sinusoids','Finding derivatives','Finding integrals',2);
ins($con,'math',7,'Functions & Equations','What is the Laplace transform used for?','Solving algebraic equations','Solving differential equations','Finding derivatives','Finding integrals',2);
ins($con,'math',7,'Functions & Equations','What is a "fixed point" of f?','f(x) = 0','f(x) = x','f(x) = 1','f(x) = -x',2);
ins($con,'math',7,'Functions & Equations','What is the Banach fixed point theorem?','About continuous functions','Contraction mappings have unique fixed points','About differentiable functions','About integrable functions',2);
ins($con,'math',7,'Functions & Equations','What is the intermediate value theorem?','About derivatives','Continuous function takes all values between f(a) and f(b)','About integrals','About limits',2);
// Level 8
ins($con,'math',8,'Functions & Equations','What is the implicit function theorem?','About explicit functions','Conditions for solving F(x,y)=0 for y as function of x','About derivatives','About integrals',2);
ins($con,'math',8,'Functions & Equations','What is the inverse function theorem?','About explicit functions','Conditions for local invertibility of a function','About derivatives','About integrals',2);
ins($con,'math',8,'Functions & Equations','What is a "functional equation"?','An equation with functions as unknowns','An equation with numbers','An equation with matrices','An equation with vectors',1);
ins($con,'math',8,'Functions & Equations','What is the Cauchy functional equation?','f(x+y) = f(x)f(y)','f(x+y) = f(x) + f(y)','f(xy) = f(x) + f(y)','f(xy) = f(x)f(y)',2);
ins($con,'math',8,'Functions & Equations','What is the Weierstrass approximation theorem?','About derivatives','Continuous functions can be uniformly approximated by polynomials','About integrals','About limits',2);
// Level 9
ins($con,'math',9,'Functions & Equations','What is the Stone-Weierstrass theorem?','About polynomials only','Generalization of Weierstrass theorem to subalgebras','About derivatives','About integrals',2);
ins($con,'math',9,'Functions & Equations','What is the Baire category theorem?','About categories','Complete metric spaces are not meager','About functions','About equations',2);
ins($con,'math',9,'Functions & Equations','What is the open mapping theorem?','About closed maps','Continuous linear surjection between Banach spaces is open','About derivatives','About integrals',2);
ins($con,'math',9,'Functions & Equations','What is the closed graph theorem?','About open graphs','Linear operator with closed graph between Banach spaces is bounded','About derivatives','About integrals',2);
ins($con,'math',9,'Functions & Equations','What is the uniform boundedness principle?','About pointwise bounded functions','Pointwise bounded family of operators is uniformly bounded','About derivatives','About integrals',2);
// Level 10
ins($con,'math',10,'Functions & Equations','What is the Hahn-Banach theorem?','About Banach spaces','Extension of bounded linear functionals','About Hilbert spaces','About metric spaces',2);
ins($con,'math',10,'Functions & Equations','What is the spectral theorem?','About spectra','Normal operators on Hilbert space have orthonormal eigenbasis','About Banach spaces','About metric spaces',2);
ins($con,'math',10,'Functions & Equations','What is the Riesz representation theorem?','About Banach spaces','Continuous linear functionals on Hilbert space are inner products','About metric spaces','About topological spaces',2);
ins($con,'math',10,'Functions & Equations','What is the Lax-Milgram theorem?','About PDEs','Existence and uniqueness of solutions to bilinear forms','About ODEs','About integral equations',2);
ins($con,'math',10,'Functions & Equations','What is the Fredholm alternative?','About Fredholm operators','Either Ax=b has unique solution or Ax=0 has nontrivial solution','About Banach spaces','About Hilbert spaces',2);

// ============================================================
// MATH — Word Problems
// ============================================================
// Level 1
ins($con,'math',1,'Word Problems','Maria has 5 apples. She gives 2 to her friend. How many does she have left?','2','3','4','5',2);
ins($con,'math',1,'Word Problems','A box has 3 rows of 4 chocolates. How many chocolates are there?','7','10','12','14',3);
ins($con,'math',1,'Word Problems','Juan walks 2 km to school and 2 km back. How far does he walk in total?','2 km','3 km','4 km','5 km',3);
ins($con,'math',1,'Word Problems','A store has 10 shirts. 4 are sold. How many remain?','4','5','6','7',3);
ins($con,'math',1,'Word Problems','Ana saves 5 pesos each day. How much does she save in 3 days?','10','12','15','18',3);
// Level 2
ins($con,'math',2,'Word Problems','A train travels 60 km/h for 2 hours. How far does it travel?','100 km','110 km','120 km','130 km',3);
ins($con,'math',2,'Word Problems','A rectangle has length 8 m and width 5 m. What is its area?','13 m²','30 m²','40 m²','45 m²',3);
ins($con,'math',2,'Word Problems','If 3 pens cost 15 pesos, how much does 1 pen cost?','3 pesos','4 pesos','5 pesos','6 pesos',3);
ins($con,'math',2,'Word Problems','A class has 30 students. 12 are boys. How many are girls?','15','16','17','18',4);
ins($con,'math',2,'Word Problems','A tank holds 100 liters. It is 3/4 full. How many liters are in it?','50','65','75','80',3);
// Level 3
ins($con,'math',3,'Word Problems','A car travels 90 km in 1.5 hours. What is its speed?','50 km/h','55 km/h','60 km/h','65 km/h',3);
ins($con,'math',3,'Word Problems','A shirt costs 250 pesos. It is on 20% discount. What is the sale price?','180','190','200','210',3);
ins($con,'math',3,'Word Problems','Two numbers sum to 20 and their difference is 4. What are the numbers?','7 and 13','8 and 12','9 and 11','10 and 10',2);
ins($con,'math',3,'Word Problems','A worker earns 500 pesos/day. How much in 22 working days?','10,000','10,500','11,000','11,500',3);
ins($con,'math',3,'Word Problems','A pipe fills a tank in 4 hours. What fraction is filled in 1 hour?','1/2','1/3','1/4','1/5',3);
// Level 4
ins($con,'math',4,'Word Problems','A and B can finish a job in 6 and 12 days respectively. How long together?','3 days','4 days','5 days','6 days',2);
ins($con,'math',4,'Word Problems','A mixture of 40% alcohol and 60% water is 200 mL. How much alcohol?','60 mL','70 mL','80 mL','90 mL',3);
ins($con,'math',4,'Word Problems','A boat goes 30 km upstream in 3 hours and 30 km downstream in 2 hours. What is the current speed?','2.5 km/h','3 km/h','3.5 km/h','4 km/h',1);
ins($con,'math',4,'Word Problems','The sum of three consecutive integers is 48. What is the largest?','15','16','17','18',3);
ins($con,'math',4,'Word Problems','A 10% salt solution and a 30% salt solution are mixed to get 20 L of 15% solution. How much of the 10% solution?','10 L','12 L','15 L','18 L',3);
// Level 5
ins($con,'math',5,'Word Problems','A ball is thrown upward with v₀=20 m/s. When does it reach max height? (g=10 m/s²)','1 s','2 s','3 s','4 s',2);
ins($con,'math',5,'Word Problems','A 5% interest rate compounded annually. What is 10,000 pesos after 2 years?','11,000','11,025','11,050','11,100',2);
ins($con,'math',5,'Word Problems','A ladder 10 m long leans against a wall. Its base is 6 m from the wall. How high does it reach?','6 m','7 m','8 m','9 m',3);
ins($con,'math',5,'Word Problems','Two trains start 300 km apart and travel toward each other at 60 and 90 km/h. When do they meet?','2 hours','3 hours','4 hours','5 hours',1);
ins($con,'math',5,'Word Problems','A store marks up items 40% then gives 20% discount. What is the net change?','12% increase','12% decrease','8% increase','8% decrease',1);
// Level 6
ins($con,'math',6,'Word Problems','A population grows at 5% per year. Starting at 1000, what is it after 3 years?','1150','1157.6','1200','1250',2);
ins($con,'math',6,'Word Problems','A 20 m rope is cut into pieces of 1.5 m. How many pieces and what is the remainder?','13 pieces, 0.5 m','13 pieces, 1 m','14 pieces, 0 m','12 pieces, 2 m',1);
ins($con,'math',6,'Word Problems','A tank is filled by pipe A in 3 hours and drained by pipe B in 5 hours. How long to fill if both open?','7.5 hours','8 hours','8.5 hours','9 hours',1);
ins($con,'math',6,'Word Problems','A man is 3 times his son\'s age. In 10 years, he will be twice his son\'s age. How old is the son now?','10','12','15','20',1);
ins($con,'math',6,'Word Problems','A 12% solution and pure water are mixed to get 8 L of 9% solution. How much pure water?','2 L','2.5 L','3 L','3.5 L',1);
// Level 7
ins($con,'math',7,'Word Problems','A ball dropped from 100 m bounces to 60% of its height each time. Total distance traveled?','400 m','450 m','500 m','550 m',3);
ins($con,'math',7,'Word Problems','A clock shows 3:00. What is the angle between the hands?','60°','75°','90°','120°',3);
ins($con,'math',7,'Word Problems','A 100 m² room needs tiles of 25 cm × 25 cm. How many tiles?','1400','1500','1600','1700',3);
ins($con,'math',7,'Word Problems','A car depreciates 15% per year. Starting at 500,000 pesos, what is its value after 2 years?','360,000','361,250','362,500','365,000',2);
ins($con,'math',7,'Word Problems','A 5-digit number is divisible by 9. The sum of its digits is 36. How many such numbers exist?','Many','Exactly one','None','Exactly two',1);
// Level 8
ins($con,'math',8,'Word Problems','A river flows at 3 km/h. A boat can go 12 km/h in still water. Time to go 45 km upstream?','5 hours','4.5 hours','4 hours','3.5 hours',1);
ins($con,'math',8,'Word Problems','A 30-60-90 triangle has hypotenuse 10. What is the shortest side?','5','5√3','10/√3','10',1);
ins($con,'math',8,'Word Problems','A sphere and cylinder have the same radius and height = diameter. Ratio of volumes?','2:3','1:2','3:2','2:1',1);
ins($con,'math',8,'Word Problems','A 1000 peso investment doubles every 7 years. How much after 21 years?','6000','7000','8000','9000',3);
ins($con,'math',8,'Word Problems','Three workers can finish a job in 4, 6, and 12 days. How long working together?','2 days','2.5 days','3 days','3.5 days',1);
// Level 9
ins($con,'math',9,'Word Problems','A snail climbs 3 m up a 10 m pole each day but slides 2 m each night. On which day does it reach the top?','7th','8th','9th','10th',2);
ins($con,'math',9,'Word Problems','A 100-liter tank is 40% full. Water is added at 5 L/min and drained at 3 L/min. When is it full?','30 min','32 min','35 min','40 min',1);
ins($con,'math',9,'Word Problems','A man walks N 30° E for 10 km then due East for 5 km. How far is he from start?','14.1 km','13.2 km','15 km','12.5 km',1);
ins($con,'math',9,'Word Problems','A 20% profit is made on cost price. If selling price is 1200, what is the cost price?','900','950','1000','1050',3);
ins($con,'math',9,'Word Problems','A geometric sequence has first term 2 and ratio 3. What is the 5th term?','162','81','54','243',1);
// Level 10
ins($con,'math',10,'Word Problems','A 100 m² field is enclosed by a fence. One side is a wall. Minimum fence needed for a rectangle?','20 m','30 m','40 m','50 m',3);
ins($con,'math',10,'Word Problems','A 1000 peso loan at 12% annual interest compounded monthly. What is owed after 1 year?','1120','1126.83','1130','1140',2);
ins($con,'math',10,'Word Problems','A projectile is launched at 45° with speed 20 m/s. What is the range? (g=10 m/s²)','30 m','35 m','40 m','45 m',3);
ins($con,'math',10,'Word Problems','A 5×5 grid of points. How many rectangles can be formed?','100','200','300','400',1);
ins($con,'math',10,'Word Problems','A fair coin is flipped 10 times. What is the probability of exactly 5 heads?','63/256','252/1024','63/512','126/512',2);

// ============================================================
// FILIPINO — Gramatika
// ============================================================
// Level 1
ins($con,'filipino',1,'Gramatika','Ano ang pangngalan?','Salitang nagpapahayag ng kilos','Salitang nagbibigay-pangalan sa tao, lugar, bagay, o hayop','Salitang naglalarawan','Salitang nagpapakita ng bilang',2);
ins($con,'filipino',1,'Gramatika','Alin ang pangngalan sa mga sumusunod?','Tumakbo','Maganda','Bahay','Mabilis',3);
ins($con,'filipino',1,'Gramatika','Ano ang panguri?','Salitang nagbibigay-pangalan','Salitang naglalarawan ng pangngalan','Salitang nagpapahayag ng kilos','Salitang nagpapakita ng bilang',2);
ins($con,'filipino',1,'Gramatika','Alin ang panguri?','Kumain','Malaki','Bahay','Siya',2);
ins($con,'filipino',1,'Gramatika','Ano ang pandiwa?','Salitang nagbibigay-pangalan','Salitang naglalarawan','Salitang nagpapahayag ng kilos o kalagayan','Salitang nagpapakita ng bilang',3);
// Level 2
ins($con,'filipino',2,'Gramatika','Alin ang tamang pangungusap?','Kumain si Maria ng mansanas.','Si Maria kumain ng mansanas.','Mansanas kumain si Maria.','Ng mansanas kumain si Maria.',1);
ins($con,'filipino',2,'Gramatika','Ano ang panlapi?','Salitang nagsasarili','Morpema na idinadagdag sa salita','Pangngalan','Pandiwa',2);
ins($con,'filipino',2,'Gramatika','Alin ang may unlapi?','Bahay','Kumain','Maganda','Mabilis',2);
ins($con,'filipino',2,'Gramatika','Ano ang gitlapi?','Panlaping inilalagay sa simula','Panlaping inilalagay sa gitna','Panlaping inilalagay sa dulo','Panlaping inilalagay sa labas',2);
ins($con,'filipino',2,'Gramatika','Alin ang may hulapi?','Kumain','Bahayan','Maganda','Tumakbo',2);
// Level 3
ins($con,'filipino',3,'Gramatika','Ano ang paksa ng pangungusap na "Ang bata ay natutulog"?','natutulog','Ang bata','ay','bata',2);
ins($con,'filipino',3,'Gramatika','Ano ang panaguri ng "Ang guro ay matalino"?','Ang guro','ay','matalino','guro',3);
ins($con,'filipino',3,'Gramatika','Alin ang panghalip panao?','Siya','Dito','Ito','Kanila',1);
ins($con,'filipino',3,'Gramatika','Ano ang uri ng pangungusap na "Kumain ka na ba?"?','Pasalaysay','Patanong','Pautos','Padamdam',2);
ins($con,'filipino',3,'Gramatika','Alin ang pangungusap na pautos?','Kumain siya.','Kumain ka na!','Kumain ka na ba?','Kumain na siya.',2);
// Level 4
ins($con,'filipino',4,'Gramatika','Ano ang aspeto ng pandiwang "kumakain"?','Naganap','Nagaganap','Magaganap','Katatapos',2);
ins($con,'filipino',4,'Gramatika','Alin ang pandiwa sa aspetong naganap?','Kumakain','Kakain','Kumain','Kakainin',3);
ins($con,'filipino',4,'Gramatika','Ano ang pokus ng pandiwang "binili"?','Pokus sa aktor','Pokus sa layon','Pokus sa direksyon','Pokus sa kagamitan',2);
ins($con,'filipino',4,'Gramatika','Alin ang tamang gamit ng "ng" at "nang"?','Kumain ng kanin nang mabilis.','Kumain nang kanin ng mabilis.','Kumain ng kanin ng mabilis.','Kumain nang kanin nang mabilis.',1);
ins($con,'filipino',4,'Gramatika','Ano ang pang-abay?','Salitang naglalarawan ng pangngalan','Salitang nagbibigay-karagdagang impormasyon sa pandiwa','Salitang nagbibigay-pangalan','Salitang nagpapahayag ng kilos',2);
// Level 5
ins($con,'filipino',5,'Gramatika','Ano ang tamang anyo ng panghalip sa "Ibinigay ___ ang libro kay Maria"?','ko','niya','namin','nila',1);
ins($con,'filipino',5,'Gramatika','Alin ang tamang gamit ng "sa" at "kay"?','Pumunta siya sa Juan.','Pumunta siya kay Juan.','Pumunta siya sa Juan at kay Maria.','Pumunta siya kay Juan at sa Maria.',2);
ins($con,'filipino',5,'Gramatika','Ano ang katumbas ng "I" sa Filipino?','Ako','Ikaw','Siya','Kami',1);
ins($con,'filipino',5,'Gramatika','Alin ang tamang pangungusap?','Ang libro ay binasa ni Maria.','Ang libro ay binasa ng Maria.','Ang libro ay binasa kay Maria.','Ang libro ay binasa sa Maria.',1);
ins($con,'filipino',5,'Gramatika','Ano ang kahulugan ng "maka-" bilang panlapi?','Nagpapahayag ng kakayahan','Nagpapahayag ng direksyon','Nagpapahayag ng paulit-ulit','Nagpapahayag ng kasalukuyan',1);
// Level 6
ins($con,'filipino',6,'Gramatika','Ano ang "di-tuwirang layon"?','Ang tumatanggap ng kilos','Ang pinagtutuunan ng kilos','Ang gumagawa ng kilos','Ang kagamitan sa kilos',2);
ins($con,'filipino',6,'Gramatika','Alin ang tamang gamit ng "man" at "naman"?','Pumunta man siya, hindi siya nakarating.','Pumunta naman siya, hindi siya nakarating.','Pumunta man siya naman, hindi siya nakarating.','Pumunta siya man naman, hindi siya nakarating.',1);
ins($con,'filipino',6,'Gramatika','Ano ang "pagtutulad"?','Paghahambing gamit ang "tulad ng" o "gaya ng"','Paghahambing gamit ang "mas"','Paghahambing gamit ang "pinaka"','Paghahambing gamit ang "kaysa"',1);
ins($con,'filipino',6,'Gramatika','Alin ang tamang anyo ng pangngalan sa maramihan?','Mga bata','Mga-bata','Mga mga bata','Bata-bata',1);
ins($con,'filipino',6,'Gramatika','Ano ang "pangngalang kolektibo"?','Pangngalang tumutukoy sa isang tao','Pangngalang tumutukoy sa grupo','Pangngalang tumutukoy sa lugar','Pangngalang tumutukoy sa bagay',2);
// Level 7
ins($con,'filipino',7,'Gramatika','Ano ang "salitang ugat"?','Salitang may panlapi','Pangunahing anyo ng salita bago dagdagan ng panlapi','Salitang hiniram','Salitang tambalan',2);
ins($con,'filipino',7,'Gramatika','Alin ang tamang gamit ng "din" at "rin"?','Pumunta rin siya.','Pumunta din siya.','Pumunta rin din siya.','Pumunta din rin siya.',1);
ins($con,'filipino',7,'Gramatika','Ano ang "salitang tambalan"?','Salitang may panlapi','Salitang binuo mula sa dalawa o higit pang salita','Salitang hiniram','Salitang may gitlapi',2);
ins($con,'filipino',7,'Gramatika','Alin ang tamang gamit ng "daw" at "raw"?','Sabi niya raw, pupunta siya.','Sabi niya daw, pupunta siya.','Sabi niya raw daw, pupunta siya.','Sabi niya daw raw, pupunta siya.',1);
ins($con,'filipino',7,'Gramatika','Ano ang "pang-angkop"?','Panlapi','Salitang nag-uugnay ng pang-uri o pang-abay sa salitang tinutukoy nito','Panghalip','Pangatnig',2);
// Level 8
ins($con,'filipino',8,'Gramatika','Ano ang "anaphora" sa Filipino?','Pag-uulit ng salita sa simula ng magkakasunod na pangungusap','Pag-uulit ng salita sa dulo','Paggamit ng tayutay','Paggamit ng panlapi',1);
ins($con,'filipino',8,'Gramatika','Alin ang tamang gamit ng "kung" at "kapag"?','Kung umuulan, nagdadala ako ng payong.','Kapag umuulan, nagdadala ako ng payong.','Kung umuulan kapag, nagdadala ako ng payong.','Kapag kung umuulan, nagdadala ako ng payong.',2);
ins($con,'filipino',8,'Gramatika','Ano ang "pagtutulad" bilang tayutay?','Paghahambing nang walang "tulad ng"','Paghahambing gamit ang "tulad ng" o "gaya ng"','Pagbibigay ng katangiang pantao sa bagay','Pagmamalabis',2);
ins($con,'filipino',8,'Gramatika','Alin ang tamang anyo ng panghalip na "kami" sa pormal?','Kami','Kaming','Namin','Amin',1);
ins($con,'filipino',8,'Gramatika','Ano ang pagkakaiba ng "kami" at "tayo"?','Walang pagkakaiba','Kami ay eksklusibo; tayo ay inklusibo','Kami ay inklusibo; tayo ay eksklusibo','Kami ay pormal; tayo ay impormal',2);
// Level 9
ins($con,'filipino',9,'Gramatika','Ano ang "polisemi"?','Salitang may iisang kahulugan','Salitang may maraming kahulugan','Salitang hiniram','Salitang tambalan',2);
ins($con,'filipino',9,'Gramatika','Alin ang tamang gamit ng "habang" at "samantalang"?','Habang naglalaro siya, natutulog ang kapatid niya.','Samantalang naglalaro siya, natutulog ang kapatid niya.','Habang samantalang naglalaro siya, natutulog ang kapatid niya.','Samantalang habang naglalaro siya, natutulog ang kapatid niya.',1);
ins($con,'filipino',9,'Gramatika','Ano ang "eufemismo"?','Paggamit ng malupit na salita','Paggamit ng malambot na salita para sa masakit na katotohanan','Paggamit ng tayutay','Paggamit ng panlapi',2);
ins($con,'filipino',9,'Gramatika','Alin ang tamang gamit ng "upang" at "para"?','Pumunta siya upang makita ang kaibigan.','Pumunta siya para makita ang kaibigan.','Pumunta siya upang para makita ang kaibigan.','Pumunta siya para upang makita ang kaibigan.',1);
ins($con,'filipino',9,'Gramatika','Ano ang "morpolohiya"?','Pag-aaral ng tunog','Pag-aaral ng kayarian ng salita','Pag-aaral ng pangungusap','Pag-aaral ng kahulugan',2);
// Level 10
ins($con,'filipino',10,'Gramatika','Ano ang "sintaksis"?','Pag-aaral ng tunog','Pag-aaral ng kayarian ng salita','Pag-aaral ng kayarian ng pangungusap','Pag-aaral ng kahulugan',3);
ins($con,'filipino',10,'Gramatika','Alin ang tamang gamit ng "bagaman" at "kahit"?','Bagaman mahirap, nagsumikap siya.','Kahit mahirap, nagsumikap siya.','Bagaman kahit mahirap, nagsumikap siya.','Kahit bagaman mahirap, nagsumikap siya.',1);
ins($con,'filipino',10,'Gramatika','Ano ang "pragmatika"?','Pag-aaral ng tunog','Pag-aaral ng kahulugan ng salita','Pag-aaral ng paggamit ng wika sa konteksto','Pag-aaral ng kayarian ng salita',3);
ins($con,'filipino',10,'Gramatika','Alin ang tamang gamit ng "kaya" at "kaya naman"?','Mahirap siya kaya nagsumikap.','Mahirap siya kaya naman nagsumikap.','Mahirap siya kaya kaya naman nagsumikap.','Mahirap siya kaya naman kaya nagsumikap.',1);
ins($con,'filipino',10,'Gramatika','Ano ang "semantika"?','Pag-aaral ng tunog','Pag-aaral ng kahulugan ng salita at pangungusap','Pag-aaral ng kayarian ng salita','Pag-aaral ng kayarian ng pangungusap',2);

// ============================================================
// FILIPINO — Panitikan
// ============================================================
// Level 1
ins($con,'filipino',1,'Panitikan','Ano ang "alamat"?','Totoong kwento','Kwentong nagpapaliwanag ng pinagmulan ng isang bagay','Tula','Dula',2);
ins($con,'filipino',1,'Panitikan','Ano ang "pabula"?','Mahabang nobela','Maikling kwentong may aral, kadalasang may hayop na tauhan','Tula','Dula',2);
ins($con,'filipino',1,'Panitikan','Ano ang "awit"?','Mahabang tula','Maikling kwento','Tulang may sukat at tugma na inaawitang may musika','Dula',3);
ins($con,'filipino',1,'Panitikan','Ano ang "korido"?','Maikling kwento','Mahabang tulang pasalaysay tungkol sa mga bayani','Tula','Dula',2);
ins($con,'filipino',1,'Panitikan','Sino ang itinuturing na "Ama ng Wikang Filipino"?','Jose Rizal','Lope K. Santos','Balagtas','Andres Bonifacio',2);
// Level 2
ins($con,'filipino',2,'Panitikan','Sino ang sumulat ng "Florante at Laura"?','Jose Rizal','Francisco Balagtas','Lope K. Santos','Amado V. Hernandez',2);
ins($con,'filipino',2,'Panitikan','Ano ang "balagtasan"?','Uri ng tula','Patimpalak sa tulang pasalaysay','Maikling kwento','Dula',2);
ins($con,'filipino',2,'Panitikan','Ano ang "haiku"?','Mahabang tula','Maikling tulang Hapon na may 5-7-5 na pantig','Maikling kwento','Dula',2);
ins($con,'filipino',2,'Panitikan','Ano ang "soneto"?','Tulang may 10 linya','Tulang may 14 na linya','Tulang may 20 na linya','Tulang may 5 na linya',2);
ins($con,'filipino',2,'Panitikan','Ano ang "epiko"?','Maikling tula','Mahabang tulang pasalaysay tungkol sa mga bayani','Maikling kwento','Dula',2);
// Level 3
ins($con,'filipino',3,'Panitikan','Ano ang "tayutay"?','Gramatikang panuntunan','Mga pigura ng pananalita na nagpapaganda ng wika','Uri ng pangungusap','Uri ng salita',2);
ins($con,'filipino',3,'Panitikan','Ano ang "simile" sa Filipino?','Paghahambing nang walang "tulad ng"','Paghahambing gamit ang "tulad ng" o "gaya ng"','Pagbibigay ng katangiang pantao sa bagay','Pagmamalabis',2);
ins($con,'filipino',3,'Panitikan','Ano ang "metapora"?','Paghahambing gamit ang "tulad ng"','Direktang paghahambing nang hindi gumagamit ng "tulad ng"','Pagbibigay ng katangiang pantao sa bagay','Pagmamalabis',2);
ins($con,'filipino',3,'Panitikan','Ano ang "personipikasyon"?','Paghahambing gamit ang "tulad ng"','Direktang paghahambing','Pagbibigay ng katangiang pantao sa bagay o hayop','Pagmamalabis',3);
ins($con,'filipino',3,'Panitikan','Ano ang "pagmamalabis" o "hiperbola"?','Paghahambing','Labis na pagpapalaki o pagpapaliit ng katotohanan','Pagbibigay ng katangiang pantao','Paghahambing gamit ang "tulad ng"',2);
// Level 4
ins($con,'filipino',4,'Panitikan','Sino ang sumulat ng "Noli Me Tangere"?','Francisco Balagtas','Lope K. Santos','Jose Rizal','Amado V. Hernandez',3);
ins($con,'filipino',4,'Panitikan','Ano ang tema ng "Florante at Laura"?','Pag-ibig at kalikasan','Kalayaan, pag-ibig, at katarungan','Kalikasan at kapaligiran','Kasaysayan ng Pilipinas',2);
ins($con,'filipino',4,'Panitikan','Ano ang "maikling kwento"?','Mahabang akdang pampanitikan','Maikling akdang may iisang tema at limitadong tauhan','Tula','Dula',2);
ins($con,'filipino',4,'Panitikan','Ano ang "nobela"?','Maikling kwento','Mahabang akdang pampanitikang may kumplikadong plot','Tula','Dula',2);
ins($con,'filipino',4,'Panitikan','Ano ang "dula"?','Tula','Maikling kwento','Akdang pampanitikang inilalahad sa pamamagitan ng diyalogo at aksyon','Nobela',3);
// Level 5
ins($con,'filipino',5,'Panitikan','Ano ang "imahen" sa panitikan?','Tayutay','Malinaw na paglalarawan na lumilikha ng larawan sa isip','Uri ng tula','Uri ng kwento',2);
ins($con,'filipino',5,'Panitikan','Ano ang "simbolismo"?','Paggamit ng tayutay','Paggamit ng simbolo upang kumatawan sa ideya o konsepto','Uri ng tula','Uri ng kwento',2);
ins($con,'filipino',5,'Panitikan','Ano ang "irony" sa Filipino?','Pagmamalabis','Pagsasabi ng kabaligtaran ng tunay na kahulugan','Paghahambing','Personipikasyon',2);
ins($con,'filipino',5,'Panitikan','Ano ang "flashback"?','Pagtingin sa hinaharap','Pagbabalik sa nakaraang pangyayari sa loob ng kwento','Paglalarawan ng tagpuan','Paglalarawan ng tauhan',2);
ins($con,'filipino',5,'Panitikan','Ano ang "foreshadowing"?','Pagbabalik sa nakaraan','Pahiwatig ng mga darating na pangyayari','Paglalarawan ng tagpuan','Paglalarawan ng tauhan',2);
// Level 6
ins($con,'filipino',6,'Panitikan','Ano ang "punto de bista"?','Tagpuan ng kwento','Pananaw kung saan isinasalaysay ang kwento','Tema ng kwento','Tauhan ng kwento',2);
ins($con,'filipino',6,'Panitikan','Ano ang "unang taong pananaw"?','Ang kwento ay isinasalaysay ng isang tauhan gamit ang "ako"','Ang kwento ay isinasalaysay ng isang tagamasid','Ang kwento ay isinasalaysay ng isang makapangyarihang tagamasid','Ang kwento ay isinasalaysay ng maraming tauhan',1);
ins($con,'filipino',6,'Panitikan','Ano ang "ikatlong taong makapangyarihang pananaw"?','Ang kwento ay isinasalaysay ng isang tauhan','Ang tagamasid ay nakakaalam ng lahat ng iniisip ng mga tauhan','Ang tagamasid ay limitado ang kaalaman','Ang kwento ay isinasalaysay ng maraming tauhan',2);
ins($con,'filipino',6,'Panitikan','Ano ang "banghay" ng kwento?','Tagpuan','Tauhan','Sunud-sunod na mga pangyayari','Tema',3);
ins($con,'filipino',6,'Panitikan','Ano ang "tunggalian" sa kwento?','Tagpuan','Pakikibaka ng mga puwersang magkasalungat','Tema','Tauhan',2);
// Level 7
ins($con,'filipino',7,'Panitikan','Ano ang "realismo" sa panitikan?','Paglalarawan ng perpektong mundo','Tapat na paglalarawan ng katotohanan ng buhay','Paggamit ng mahiwagang elemento','Paglalarawan ng kasaysayan',2);
ins($con,'filipino',7,'Panitikan','Ano ang "romantisismo"?','Tapat na paglalarawan ng katotohanan','Pagbibigay-diin sa emosyon, imahinasyon, at kalikasan','Paggamit ng mahiwagang elemento','Paglalarawan ng kasaysayan',2);
ins($con,'filipino',7,'Panitikan','Ano ang "modernismo" sa panitikan?','Tradisyonal na pamamaraan','Eksperimental na pamamaraan na tumatanggal sa tradisyon','Paggamit ng mahiwagang elemento','Paglalarawan ng kasaysayan',2);
ins($con,'filipino',7,'Panitikan','Ano ang "postkolonyal na panitikan"?','Panitikan ng mga mananakop','Panitikan na tumutugon sa epekto ng kolonyalismo','Panitikan ng kasaysayan','Panitikan ng modernismo',2);
ins($con,'filipino',7,'Panitikan','Ano ang "stream of consciousness" sa Filipino?','Diyalogo','Patuloy na daloy ng mga iniisip ng tauhan','Paglalarawan ng tagpuan','Paglalarawan ng tauhan',2);
// Level 8
ins($con,'filipino',8,'Panitikan','Sino ang sumulat ng "Ibong Adarna"?','Jose Rizal','Francisco Balagtas','Hindi tiyak ang may-akda','Lope K. Santos',3);
ins($con,'filipino',8,'Panitikan','Ano ang "epikong bayan"?','Maikling kwento','Mahabang tulang pasalaysay na nagmula sa tradisyong oral ng isang komunidad','Tula','Dula',2);
ins($con,'filipino',8,'Panitikan','Ano ang "Biag ni Lam-ang"?','Maikling kwento','Epikong bayan ng mga Ilokano','Tula','Dula',2);
ins($con,'filipino',8,'Panitikan','Ano ang "Darangen"?','Maikling kwento','Epikong bayan ng mga Maranao','Tula','Dula',2);
ins($con,'filipino',8,'Panitikan','Ano ang "Hudhud"?','Maikling kwento','Epikong bayan ng mga Ifugao','Tula','Dula',2);
// Level 9
ins($con,'filipino',9,'Panitikan','Ano ang "intertekstwalidad"?','Pagsulat ng bagong teksto','Relasyon at sanggunian sa pagitan ng mga teksto','Paggamit ng tayutay','Paggamit ng simbolo',2);
ins($con,'filipino',9,'Panitikan','Ano ang "dekonstruksyon" sa panitikan?','Pagbuo ng kwento','Pagsusuri ng mga nakatagong pagpapalagay sa teksto','Pagsasalaysay ng kwento','Paglalarawan ng tauhan',2);
ins($con,'filipino',9,'Panitikan','Ano ang "feminismong pampanitikan"?','Pagwawalang-bahala sa kasarian','Pagsusuri ng panitikan sa pamamagitan ng lente ng kasarian at kapangyarihan','Pagsusuri ng kasaysayan','Pagsusuri ng wika',2);
ins($con,'filipino',9,'Panitikan','Ano ang "ekokritikal na pagsusuri"?','Pagsusuri ng ekonomiya','Pagsusuri ng kalikasan at kapaligiran sa panitikan','Pagsusuri ng kasaysayan','Pagsusuri ng wika',2);
ins($con,'filipino',9,'Panitikan','Ano ang "metapiksyon"?','Maikling kwento','Piksyong malay sa sariling katangiang pampanitikan','Tula','Dula',2);
// Level 10
ins($con,'filipino',10,'Panitikan','Ano ang "hermeneutika" sa panitikan?','Pag-aaral ng gramatika','Teorya at metodolohiya ng interpretasyon ng teksto','Pag-aaral ng talasalitaan','Pag-aaral ng bantas',2);
ins($con,'filipino',10,'Panitikan','Ano ang "dialogismo" (Bakhtin)?','Iisang boses','Maraming boses at pananaw sa teksto','Uri ng gramatika','Pamamaraan ng pagsasalaysay',2);
ins($con,'filipino',10,'Panitikan','Ano ang "kamatayan ng may-akda" (Barthes)?','Mahalaga ang may-akda','Ang intensyon ng may-akda ay hindi mahalaga sa kahulugan','Uri ng irony','Pamamaraan ng pagsasalaysay',2);
ins($con,'filipino',10,'Panitikan','Ano ang "mimesis" sa panitikan?','Pagmamalabis','Pagtulad o representasyon ng katotohanan sa sining','Uri ng irony','Pamamaraan ng pagsasalaysay',2);
ins($con,'filipino',10,'Panitikan','Ano ang "palimpsest" bilang konsepto sa panitikan?','Bagong teksto','Teksto na nagtataglay ng bakas ng naunang pagsulat','Uri ng tula','Kayarian ng pangungusap',2);

// ============================================================
// FILIPINO — Pag-unawa sa Binasa
// ============================================================
// Level 1
ins($con,'filipino',1,'Pag-unawa sa Binasa','Ano ang "pangunahing ideya"?','Ang huling pangungusap','Ang pinakamahalagang punto ng talata','Isang detalye','Ang pamagat',2);
ins($con,'filipino',1,'Pag-unawa sa Binasa','Ano ang "tagpuan" ng kwento?','Ang mga tauhan','Kung saan at kailan nagaganap ang kwento','Ang suliranin','Ang solusyon',2);
ins($con,'filipino',1,'Pag-unawa sa Binasa','Ano ang "tauhan" ng kwento?','Ang lugar','Ang oras','Ang tao o hayop sa kwento','Ang pangyayari',3);
ins($con,'filipino',1,'Pag-unawa sa Binasa','Ano ang "suliranin" ng kwento?','Ang tagpuan','Ang tauhan','Ang problema sa kwento','Ang solusyon',3);
ins($con,'filipino',1,'Pag-unawa sa Binasa','Ano ang "solusyon" ng kwento?','Ang suliranin','Ang paraan ng paglutas ng suliranin','Ang tagpuan','Ang tauhan',2);
// Level 2
ins($con,'filipino',2,'Pag-unawa sa Binasa','Ano ang "implikasyon"?','Direktang pahayag','Kahulugang ipinahiwatig ngunit hindi direktang sinabi','Buod','Tanong',2);
ins($con,'filipino',2,'Pag-unawa sa Binasa','Ano ang "konteksto"?','Diksyunaryo','Impormasyon sa paligid ng salita na tumutulong sa pag-unawa','Gramatikang panuntunan','Bantas',2);
ins($con,'filipino',2,'Pag-unawa sa Binasa','Ano ang "tema" ng kwento?','Ang pangunahing tauhan','Ang tagpuan','Ang sentral na mensahe o aral','Ang banghay',3);
ins($con,'filipino',2,'Pag-unawa sa Binasa','Ano ang "buod"?','Mahabang muling pagsasalaysay','Maikling pahayag ng mga pangunahing punto','Listahan ng mga tauhan','Paglalarawan ng tagpuan',2);
ins($con,'filipino',2,'Pag-unawa sa Binasa','Ano ang "sanhi at bunga"?','Dalawang walang kaugnayan na pangyayari','Kung bakit nangyari ang isang bagay at ang resulta nito','Uri ng tauhan','Kayarian ng kwento',2);
// Level 3
ins($con,'filipino',3,'Pag-unawa sa Binasa','Ano ang pagkakaiba ng katotohanan at opinyon?','Walang pagkakaiba','Ang katotohanan ay mapapatunayan; ang opinyon ay personal na pananaw','Ang katotohanan ay opinyon','Ang opinyon ay katotohanan',2);
ins($con,'filipino',3,'Pag-unawa sa Binasa','Ano ang "layunin ng may-akda"?','Ang tagpuan ng kwento','Kung bakit isinulat ng may-akda ang teksto','Ang pangunahing tauhan','Ang banghay',2);
ins($con,'filipino',3,'Pag-unawa sa Binasa','Ano ang "punto de bista"?','Ang tagpuan','Ang pananaw kung saan isinasalaysay ang kwento','Ang tema','Ang tunggalian',2);
ins($con,'filipino',3,'Pag-unawa sa Binasa','Ano ang "kayarian ng teksto"?','Ang font na ginamit','Kung paano nakaayos ang teksto','Ang bilang ng talata','Ang pamagat',2);
ins($con,'filipino',3,'Pag-unawa sa Binasa','Ano ang "sumusuportang detalye"?','Ang pangunahing ideya','Impormasyon na sumusuporta sa pangunahing ideya','Ang konklusyon','Ang panimula',2);
// Level 4
ins($con,'filipino',4,'Pag-unawa sa Binasa','Ano ang "tayutay" bilang tulong sa pag-unawa?','Gramatikang panuntunan','Mga pigura ng pananalita na nagpapayaman ng kahulugan','Uri ng pangungusap','Uri ng salita',2);
ins($con,'filipino',4,'Pag-unawa sa Binasa','Ano ang "tono" ng teksto?','Dami ng salita','Saloobin ng may-akda sa paksa','Tagpuan','Banghay',2);
ins($con,'filipino',4,'Pag-unawa sa Binasa','Ano ang "mood" ng teksto?','Saloobin ng may-akda','Damdaming nalilikha sa mambabasa','Emosyon ng tauhan','Tagpuan',2);
ins($con,'filipino',4,'Pag-unawa sa Binasa','Ano ang "foreshadowing"?','Buod','Pahiwatig ng mga darating na pangyayari','Flashback','Paglalarawan',2);
ins($con,'filipino',4,'Pag-unawa sa Binasa','Ano ang "irony"?','Direktang pahayag','Pagsasabi ng kabaligtaran ng tunay na kahulugan','Uri ng tayutay','Uri ng tono',2);
// Level 5
ins($con,'filipino',5,'Pag-unawa sa Binasa','Ano ang "bias" sa teksto?','Balanseng pananaw','Hindi patas na pagpabor sa isang panig','Uri ng ebidensya','Buod',2);
ins($con,'filipino',5,'Pag-unawa sa Binasa','Ano ang "konotasyon"?','Literal na kahulugan','Emosyonal na kahulugan ng salita','Gramatikang panuntunan','Bantas',2);
ins($con,'filipino',5,'Pag-unawa sa Binasa','Ano ang "denotasyon"?','Emosyonal na kahulugan','Literal na kahulugan sa diksyunaryo','Tayutay','Implikasyon',2);
ins($con,'filipino',5,'Pag-unawa sa Binasa','Ano ang "tekstwal na ebidensya"?','Opinyon','Tiyak na detalye mula sa teksto upang suportahan ang pahayag','Buod','Hula',2);
ins($con,'filipino',5,'Pag-unawa sa Binasa','Ano ang "pagpapakilala ng tauhan"?','Paglalarawan ng tagpuan','Kung paano binubuo ng may-akda ang tauhan','Kayarian ng banghay','Tema',2);
// Level 6-10 (condensed for space)
ins($con,'filipino',6,'Pag-unawa sa Binasa','Ano ang "alegorya"?','Maikling tula','Kwentong may nakatagong kahulugan','Uri ng sanaysay','Uri ng dula',2);
ins($con,'filipino',6,'Pag-unawa sa Binasa','Ano ang "sintetisahin" sa pagbabasa?','Buuin ang isang teksto','Pagsamahin ang impormasyon mula sa maraming pinagkukunan','Hanapin ang pangunahing ideya','Gumawa ng implikasyon',2);
ins($con,'filipino',6,'Pag-unawa sa Binasa','Ano ang "pagsusuri ng argumento"?','Pagbubuod','Pagsusuri ng lakas at kahinaan ng mga pahayag','Paghahanap ng tema','Paghahanap ng tayutay',2);
ins($con,'filipino',6,'Pag-unawa sa Binasa','Ano ang "pagtatasa ng kredibilidad"?','Pagbubuod','Pagsusuri kung mapagkakatiwalaan ang pinagkukunan','Paghahanap ng tema','Paghahanap ng tayutay',2);
ins($con,'filipino',6,'Pag-unawa sa Binasa','Ano ang "kritikal na pagbabasa"?','Simpleng pagbabasa','Aktibong pagsusuri at pagtatasa ng teksto','Mabilis na pagbabasa','Mabagal na pagbabasa',2);
ins($con,'filipino',7,'Pag-unawa sa Binasa','Ano ang "dramatikong irony"?','Kapag ang mambabasa ay nakakaalam ng hindi alam ng tauhan','Kapag ang tauhan ay dramatiko','Kapag ang banghay ay ironic','Kapag ang tagpuan ay ironic',1);
ins($con,'filipino',7,'Pag-unawa sa Binasa','Ano ang "motif"?','Pangunahing tauhan','Paulit-ulit na elemento sa kwento','Tagpuan','Klimaks',2);
ins($con,'filipino',7,'Pag-unawa sa Binasa','Ano ang "ambiguidad" sa teksto?','Malinaw na kahulugan','Pagkakaroon ng higit sa isang posibleng interpretasyon','Gramatikang pagkakamali','Pagbabago ng banghay',2);
ins($con,'filipino',7,'Pag-unawa sa Binasa','Ano ang "hindi mapagkakatiwalaang tagapagsalaysay"?','Tagapagsalaysay na laging totoo','Tagapagsalaysay na may katanungan ang kredibilidad','Tagapagsalaysay sa ikatlong panauhan','Makapangyarihang tagapagsalaysay',2);
ins($con,'filipino',7,'Pag-unawa sa Binasa','Ano ang "intertekstwalidad"?','Pagsulat ng bagong teksto','Relasyon sa pagitan ng mga teksto','Paggamit ng tayutay','Paggamit ng simbolo',2);
ins($con,'filipino',8,'Pag-unawa sa Binasa','Ano ang "epistolaryong anyo"?','Kwentong isinasalaysay sa pamamagitan ng mga liham','Uri ng tula','Pamamaraan ng pagsasalaysay','Kayarian ng pangungusap',1);
ins($con,'filipino',8,'Pag-unawa sa Binasa','Ano ang "polipono" sa nobela?','Iisang nangingibabaw na boses','Maraming independyenteng boses o pananaw','Uri ng tagpuan','Kagamitan sa banghay',2);
ins($con,'filipino',8,'Pag-unawa sa Binasa','Ano ang "libreng di-tuwirang diskurso"?','Direktang pananalita','Pagsasama ng boses ng tagapagsalaysay at tauhan','Ikatlong taong pagsasalaysay','Unang taong pagsasalaysay',2);
ins($con,'filipino',8,'Pag-unawa sa Binasa','Ano ang "defamilyarisasyon"?','Paggawa ng pamilyar na bagay na mukhang bago','Uri ng irony','Kayarian ng banghay','Pamamaraan ng tauhan',1);
ins($con,'filipino',8,'Pag-unawa sa Binasa','Ano ang "mise en abyme"?','Uri ng tagpuan','Kwento sa loob ng kwento na sumasalamin sa pangunahing salaysay','Pamamaraan ng tauhan','Kagamitan sa gramatika',2);
ins($con,'filipino',9,'Pag-unawa sa Binasa','Ano ang "narratolohiya"?','Pag-aaral ng kayarian ng salaysay','Uri ng tula','Teorya ng gramatika','Pamamaraan ng talasalitaan',1);
ins($con,'filipino',9,'Pag-unawa sa Binasa','Ano ang "heteroglosya"?','Iisang estilo ng wika','Pagkakaiba-iba ng mga boses at estilo ng wika sa teksto','Konsepto ng gramatika','Uri ng irony',2);
ins($con,'filipino',9,'Pag-unawa sa Binasa','Ano ang "aporia" sa teoryang pampanitikan?','Malinaw na resolusyon','Hindi malulutas na kontradiksyon sa teksto','Uri ng metapora','Pamamaraan ng pagsasalaysay',2);
ins($con,'filipino',9,'Pag-unawa sa Binasa','Ano ang "parateksto"?','Ang pangunahing teksto','Mga elementong nakapaligid sa teksto (pamagat, paunang salita, atbp.)','Uri ng tauhan','Kagamitan sa banghay',2);
ins($con,'filipino',9,'Pag-unawa sa Binasa','Ano ang "palimpsest" bilang konsepto sa pagbabasa?','Bagong teksto','Teksto na nagtataglay ng bakas ng naunang pagsulat','Uri ng tula','Kayarian ng pangungusap',2);
ins($con,'filipino',10,'Pag-unawa sa Binasa','Ano ang "hermeneutika"?','Pag-aaral ng gramatika','Teorya at metodolohiya ng interpretasyon','Pag-aaral ng talasalitaan','Pag-aaral ng bantas',2);
ins($con,'filipino',10,'Pag-unawa sa Binasa','Ano ang "phenomenolohiya" sa pagbabasa?','Pag-aaral ng gramatika','Pag-aaral ng karanasan at persepsyon ng mambabasa','Pag-aaral ng talasalitaan','Pag-aaral ng bantas',2);
ins($con,'filipino',10,'Pag-unawa sa Binasa','Ano ang "reader-response theory"?','Ang may-akda ang nagbibigay ng kahulugan','Ang mambabasa ang nagbibigay ng kahulugan sa teksto','Ang teksto mismo ang nagbibigay ng kahulugan','Walang kahulugan ang teksto',2);
ins($con,'filipino',10,'Pag-unawa sa Binasa','Ano ang "new criticism"?','Modernong estilo ng pagsulat','Malapit na pagbabasa na nakatuon sa teksto mismo','Kasaysayang pamamaraan','Uri ng biograpiya',2);
ins($con,'filipino',10,'Pag-unawa sa Binasa','Ano ang "postkolonyal na pagbabasa"?','Pagbabasa ng panitikan ng mga mananakop','Pagsusuri ng epekto ng kolonyalismo sa teksto','Uri ng pantasya','Kasaysayang estilo',2);

// ============================================================
// FILIPINO — Talasalitaan
// ============================================================
// Level 1
ins($con,'filipino',1,'Talasalitaan','Ano ang kahulugan ng "masaya"?','Malungkot','Galit','Maligaya','Pagod',3);
ins($con,'filipino',1,'Talasalitaan','Alin ang salitang may kahulugang "malaki"?','Maliit','Manipis','Maluwag','Malaki',4);
ins($con,'filipino',1,'Talasalitaan','Ano ang kabaligtaran ng "mainit"?','Malamig','Mahangin','Maulap','Maaraw',1);
ins($con,'filipino',1,'Talasalitaan','Ano ang kahulugan ng "mabilis"?','Mabagal','Maingay','Matulin','Matahimik',3);
ins($con,'filipino',1,'Talasalitaan','Alin ang salitang may kahulugang "maganda"?','Pangit','Maayos','Marikit','Malinis',3);
// Level 2
ins($con,'filipino',2,'Talasalitaan','Ano ang kahulugan ng "matiyaga"?','Madaling sumuko','Mapagtiis at walang pagod','Maingay','Mapagmataas',2);
ins($con,'filipino',2,'Talasalitaan','Alin ang salitang magkasingkahulugan ng "magiting"?','Duwag','Matapang','Mahina','Maingat',2);
ins($con,'filipino',2,'Talasalitaan','Ano ang kabaligtaran ng "masipag"?','Matiyaga','Maliksi','Tamad','Maingat',3);
ins($con,'filipino',2,'Talasalitaan','Ano ang kahulugan ng "mapagkumbaba"?','Mapagmataas','Walang pagmamalaki','Maingay','Mapaghanap',2);
ins($con,'filipino',2,'Talasalitaan','Alin ang salitang may kahulugang "maliwanag"?','Madilim','Maliwanag','Malabo','Maulap',2);
// Level 3
ins($con,'filipino',3,'Talasalitaan','Ano ang kahulugan ng "mapamaraan"?','Walang paraan','Marunong gumamit ng paraan','Mapagmataas','Mapaghanap',2);
ins($con,'filipino',3,'Talasalitaan','Alin ang salitang magkasingkahulugan ng "maingat"?','Pabaya','Maingat','Maingay','Mapagmataas',2);
ins($con,'filipino',3,'Talasalitaan','Ano ang kabaligtaran ng "matapang"?','Magiting','Malakas','Duwag','Maliksi',3);
ins($con,'filipino',3,'Talasalitaan','Ano ang kahulugan ng "mapanuri"?','Walang pagsusuri','Mahilig magsuri at mag-analisa','Mapagmataas','Mapaghanap',2);
ins($con,'filipino',3,'Talasalitaan','Alin ang salitang may kahulugang "malungkot"?','Masaya','Maligaya','Malungkutin','Maaliwalas',3);
// Level 4
ins($con,'filipino',4,'Talasalitaan','Ano ang kahulugan ng "mapagkawanggawa"?','Makasarili','Mapagbigay at mapagmahal sa kapwa','Mapagmataas','Mapaghanap',2);
ins($con,'filipino',4,'Talasalitaan','Alin ang salitang magkasingkahulugan ng "matalino"?','Hangal','Matalino','Marunong','Mapagmataas',3);
ins($con,'filipino',4,'Talasalitaan','Ano ang kabaligtaran ng "mapagbigay"?','Mapagmahal','Mapagkawanggawa','Makasarili','Mapaghanap',3);
ins($con,'filipino',4,'Talasalitaan','Ano ang kahulugan ng "mapanganib"?','Ligtas','Delikado at may panganib','Maayos','Maliwanag',2);
ins($con,'filipino',4,'Talasalitaan','Alin ang salitang may kahulugang "mahalaga"?','Walang halaga','Mura','Mahalaga','Basta-basta',3);
// Level 5-10 (condensed)
ins($con,'filipino',5,'Talasalitaan','Ano ang kahulugan ng "mapagpanggap"?','Tapat','Nagpapanggap na iba ang tunay na sarili','Maingat','Mapaghanap',2);
ins($con,'filipino',5,'Talasalitaan','Alin ang salitang magkasingkahulugan ng "maliwanag"?','Malabo','Malinaw','Madilim','Maulap',2);
ins($con,'filipino',5,'Talasalitaan','Ano ang kabaligtaran ng "mapagkumbaba"?','Mapagmahal','Mapagbigay','Mapagmataas','Mapaghanap',3);
ins($con,'filipino',5,'Talasalitaan','Ano ang kahulugan ng "mapagpalaya"?','Nagbibigay ng kalayaan','Nagbibigay ng pagkakataon','Nagbibigay ng tulong','Nagbibigay ng pera',1);
ins($con,'filipino',5,'Talasalitaan','Alin ang salitang may kahulugang "mabait"?','Masamang-loob','Mabuting-loob','Mapagmataas','Mapaghanap',2);
ins($con,'filipino',6,'Talasalitaan','Ano ang kahulugan ng "mapagmahal"?','Mapoot','Puno ng pagmamahal','Mapagmataas','Mapaghanap',2);
ins($con,'filipino',6,'Talasalitaan','Alin ang salitang magkasingkahulugan ng "matapang"?','Duwag','Mahina','Magiting','Maingat',3);
ins($con,'filipino',6,'Talasalitaan','Ano ang kabaligtaran ng "masipag"?','Maliksi','Matiyaga','Maingat','Tamad',4);
ins($con,'filipino',6,'Talasalitaan','Ano ang kahulugan ng "mapagkukunwari"?','Tapat','Nagkukunwari at hindi tapat','Maingat','Mapaghanap',2);
ins($con,'filipino',6,'Talasalitaan','Alin ang salitang may kahulugang "maingay"?','Tahimik','Maingay','Maliwanag','Madilim',2);
ins($con,'filipino',7,'Talasalitaan','Ano ang kahulugan ng "mapagpalakas-loob"?','Nagpapahina ng loob','Nagbibigay ng lakas ng loob','Mapagmataas','Mapaghanap',2);
ins($con,'filipino',7,'Talasalitaan','Alin ang salitang magkasingkahulugan ng "marunong"?','Hangal','Ignorante','Matalino','Mapagmataas',3);
ins($con,'filipino',7,'Talasalitaan','Ano ang kabaligtaran ng "mapagkawanggawa"?','Mapagmahal','Mapagbigay','Makasarili','Mapaghanap',3);
ins($con,'filipino',7,'Talasalitaan','Ano ang kahulugan ng "mapagpigil"?','Walang pigil','Marunong magpigil ng sarili','Maingat','Mapaghanap',2);
ins($con,'filipino',7,'Talasalitaan','Alin ang salitang may kahulugang "maayos"?','Magulo','Maayos','Maingay','Madilim',2);
ins($con,'filipino',8,'Talasalitaan','Ano ang kahulugan ng "mapagpangarap"?','Walang pangarap','Puno ng pangarap at mithiin','Maingat','Mapaghanap',2);
ins($con,'filipino',8,'Talasalitaan','Alin ang salitang magkasingkahulugan ng "mapagkumbaba"?','Mapagmataas','Hambog','Mababang-loob','Mapaghanap',3);
ins($con,'filipino',8,'Talasalitaan','Ano ang kabaligtaran ng "matiyaga"?','Masipag','Maliksi','Maingat','Pabaya',4);
ins($con,'filipino',8,'Talasalitaan','Ano ang kahulugan ng "mapagbago"?','Ayaw magbago','Handang magbago at mag-adapt','Maingat','Mapaghanap',2);
ins($con,'filipino',8,'Talasalitaan','Alin ang salitang may kahulugang "malakas"?','Mahina','Marupok','Malakas','Malambot',3);
ins($con,'filipino',9,'Talasalitaan','Ano ang kahulugan ng "mapagpalawig"?','Nagpapaikli','Nagpapalawak at nagpapalawak ng ideya','Maingat','Mapaghanap',2);
ins($con,'filipino',9,'Talasalitaan','Alin ang salitang magkasingkahulugan ng "maingat"?','Pabaya','Walang malasakit','Maingat','Mapagmataas',3);
ins($con,'filipino',9,'Talasalitaan','Ano ang kabaligtaran ng "mapagmahal"?','Mapagbigay','Mapagkawanggawa','Mapoot','Mapaghanap',3);
ins($con,'filipino',9,'Talasalitaan','Ano ang kahulugan ng "mapagpalakas"?','Nagpapahina','Nagbibigay ng lakas','Maingat','Mapaghanap',2);
ins($con,'filipino',9,'Talasalitaan','Alin ang salitang may kahulugang "mabilis"?','Mabagal','Matulin','Maingay','Madilim',2);
ins($con,'filipino',10,'Talasalitaan','Ano ang kahulugan ng "mapagpalawig ng kaalaman"?','Nagpapaikli ng kaalaman','Nagpapalawak ng kaalaman','Maingat','Mapaghanap',2);
ins($con,'filipino',10,'Talasalitaan','Alin ang salitang magkasingkahulugan ng "matalino"?','Hangal','Ignorante','Marunong','Mapagmataas',3);
ins($con,'filipino',10,'Talasalitaan','Ano ang kabaligtaran ng "mapagpanggap"?','Mapagkunwari','Mapagmataas','Tapat','Mapaghanap',3);
ins($con,'filipino',10,'Talasalitaan','Ano ang kahulugan ng "mapagpalakas-loob"?','Nagpapahina ng loob','Nagbibigay ng lakas ng loob','Maingat','Mapaghanap',2);
ins($con,'filipino',10,'Talasalitaan','Alin ang salitang may kahulugang "mahalaga"?','Walang halaga','Mura','Basta-basta','Napakahalaga',4);

// ============================================================
// FILIPINO — Wika at Kultura
// ============================================================
// Level 1
ins($con,'filipino',1,'Wika at Kultura','Ano ang pambansang wika ng Pilipinas?','Ingles','Espanyol','Filipino','Tagalog',3);
ins($con,'filipino',1,'Wika at Kultura','Ilang opisyal na wika ang mayroon ang Pilipinas?','1','2','3','4',2);
ins($con,'filipino',1,'Wika at Kultura','Ano ang "kultura"?','Wika lamang','Kaugalian, paniniwala, at sining ng isang grupo','Kasaysayan lamang','Relihiyon lamang',2);
ins($con,'filipino',1,'Wika at Kultura','Ano ang "tradisyon"?','Bagong gawi','Mga kaugaliang ipinasa mula sa henerasyon sa henerasyon','Modernong gawi','Banyagang gawi',2);
ins($con,'filipino',1,'Wika at Kultura','Ano ang "pagpapahalaga"?','Mga bagay na walang halaga','Mga bagay na itinuturing na mahalaga ng isang grupo','Mga banyagang gawi','Mga modernong gawi',2);
// Level 2
ins($con,'filipino',2,'Wika at Kultura','Ano ang "bayanihan"?','Pakikipaglaban','Pagtulong sa kapwa nang walang bayad','Pakikipagkalakalan','Pakikipagtalo',2);
ins($con,'filipino',2,'Wika at Kultura','Ano ang "utang na loob"?','Pagkakautang ng pera','Pagkilala sa tulong na natanggap','Pakikipagtalo','Pakikipagkalakalan',2);
ins($con,'filipino',2,'Wika at Kultura','Ano ang "hiya"?','Pagmamalaki','Pagkahiya o pagpapahalaga sa dangal','Galit','Saya',2);
ins($con,'filipino',2,'Wika at Kultura','Ano ang "pakikisama"?','Pakikipaglaban','Pakikiisa at pakikipagtulungan sa grupo','Pakikipagtalo','Pakikipagkalakalan',2);
ins($con,'filipino',2,'Wika at Kultura','Ano ang "pagmamano"?','Pagbabati sa pamamagitan ng kamay','Pagbabati sa pamamagitan ng yakap','Pagbabati sa pamamagitan ng halik','Pagbabati sa pamamagitan ng ngiti',1);
// Level 3
ins($con,'filipino',3,'Wika at Kultura','Ano ang "diaspora"?','Pagbabalik sa sariling bansa','Pagkalat ng isang grupo sa iba\'t ibang lugar','Pagtitipon ng isang grupo','Pagtatayo ng bagong bansa',2);
ins($con,'filipino',3,'Wika at Kultura','Ano ang "globalisasyon" sa konteksto ng wika?','Pagpapalaki ng wika','Pagkalat ng mga wika at kultura sa buong mundo','Pagkawala ng mga wika','Paglikha ng bagong wika',2);
ins($con,'filipino',3,'Wika at Kultura','Ano ang "code-switching"?','Paggamit ng iisang wika','Paglipat-lipat ng wika sa iisang pag-uusap','Pagtuturo ng wika','Pag-aaral ng wika',2);
ins($con,'filipino',3,'Wika at Kultura','Ano ang "lingua franca"?','Sariling wika','Wikang ginagamit bilang paraan ng komunikasyon sa pagitan ng mga taong may iba\'t ibang wika','Wikang patay','Wikang bago',2);
ins($con,'filipino',3,'Wika at Kultura','Ano ang "multikulturalismo"?','Iisang kultura','Pagkilala at pagtanggap sa iba\'t ibang kultura','Pagtatanggal ng kultura','Paglikha ng bagong kultura',2);
// Level 4
ins($con,'filipino',4,'Wika at Kultura','Ano ang "kolonyalismo" at ang epekto nito sa wika?','Walang epekto','Nagdulot ng pagbabago at pagkawala ng ilang katutubong wika','Nagpalakas ng katutubong wika','Naglikha ng bagong wika',2);
ins($con,'filipino',4,'Wika at Kultura','Ano ang "Komisyon sa Wikang Filipino" (KWF)?','Isang paaralan','Ahensyang nangangasiwa sa pag-unlad ng wikang Filipino','Isang organisasyon ng mga manunulat','Isang grupo ng mga guro',2);
ins($con,'filipino',4,'Wika at Kultura','Ano ang "Buwan ng Wika"?','Enero','Agosto','Hunyo','Disyembre',2);
ins($con,'filipino',4,'Wika at Kultura','Ano ang "Ortograpiya ng Wikang Filipino"?','Isang nobela','Opisyal na gabay sa wastong pagbabaybay ng wikang Filipino','Isang tula','Isang dula',2);
ins($con,'filipino',4,'Wika at Kultura','Ano ang "Pilipino" kumpara sa "Filipino"?','Magkaparehong wika','Pilipino ang lumang pangalan; Filipino ang modernong pangalan ng pambansang wika','Filipino ang lumang pangalan; Pilipino ang modernong pangalan','Magkaibang wika',2);
// Level 5-10 (condensed)
ins($con,'filipino',5,'Wika at Kultura','Ano ang "sosyolinggwistika"?','Pag-aaral ng gramatika','Pag-aaral ng relasyon ng wika at lipunan','Pag-aaral ng kasaysayan ng wika','Pag-aaral ng talasalitaan',2);
ins($con,'filipino',5,'Wika at Kultura','Ano ang "diglosya"?','Paggamit ng iisang wika','Paggamit ng dalawang anyo ng wika sa iba\'t ibang sitwasyon','Paggamit ng maraming wika','Pagkawala ng wika',2);
ins($con,'filipino',5,'Wika at Kultura','Ano ang "pidgin"?','Pormal na wika','Simpleng wika na nabuo mula sa pakikipag-ugnayan ng dalawang grupo','Katutubong wika','Opisyal na wika',2);
ins($con,'filipino',5,'Wika at Kultura','Ano ang "creole"?','Pidgin na naging unang wika ng isang komunidad','Pormal na wika','Katutubong wika','Opisyal na wika',1);
ins($con,'filipino',5,'Wika at Kultura','Ano ang "language death"?','Paglikha ng bagong wika','Pagkawala ng isang wika dahil walang natitira pang nagsasalita nito','Pagbabago ng wika','Pagpapalawak ng wika',2);
ins($con,'filipino',6,'Wika at Kultura','Ano ang "etnolohiya"?','Pag-aaral ng wika','Pag-aaral ng mga kultura at grupo ng tao','Pag-aaral ng kasaysayan','Pag-aaral ng relihiyon',2);
ins($con,'filipino',6,'Wika at Kultura','Ano ang "oral na tradisyon"?','Nakasulat na tradisyon','Pagpapasa ng kaalaman at kultura sa pamamagitan ng salita','Modernong tradisyon','Banyagang tradisyon',2);
ins($con,'filipino',6,'Wika at Kultura','Ano ang "intangible cultural heritage"?','Materyal na kultura','Hindi materyal na kultura tulad ng tradisyon at gawi','Arkitektura','Sining biswal',2);
ins($con,'filipino',6,'Wika at Kultura','Ano ang "UNESCO" sa konteksto ng kultura?','Isang paaralan','Organisasyong nagpoprotekta ng kultura at pamana ng mundo','Isang gobyerno','Isang negosyo',2);
ins($con,'filipino',6,'Wika at Kultura','Ano ang "Hudhud" ng mga Ifugao?','Maikling kwento','Epikong bayan na idineklara ng UNESCO bilang intangible cultural heritage','Tula','Dula',2);
ins($con,'filipino',7,'Wika at Kultura','Ano ang "postkolonyal na teorya"?','Teorya ng kolonyalismo','Pagsusuri ng epekto ng kolonyalismo sa kultura at wika','Teorya ng modernismo','Teorya ng globalisasyon',2);
ins($con,'filipino',7,'Wika at Kultura','Ano ang "hybridity" (Bhabha)?','Purong kultura','Pagsasama ng dalawang kultura upang lumikha ng bagong kultura','Pagkawala ng kultura','Pagpapalawak ng kultura',2);
ins($con,'filipino',7,'Wika at Kultura','Ano ang "orientalismo" (Said)?','Tapat na paglalarawan ng Silangan','Konstruksyon ng Kanluran sa Silangan bilang "iba" at "mababa"','Pagmamahal sa Silangan','Pag-aaral ng Silangan',2);
ins($con,'filipino',7,'Wika at Kultura','Ano ang "subaltern" (Spivak)?','Ang mga nasa kapangyarihan','Ang mga marginalisado at walang boses','Ang mga edukado','Ang mga mayaman',2);
ins($con,'filipino',7,'Wika at Kultura','Ano ang "cultural imperialism"?','Pagpapalawak ng kultura','Pagpapataw ng kultura ng isang makapangyarihang bansa sa iba','Pagpapalitan ng kultura','Pagprotekta ng kultura',2);
ins($con,'filipino',8,'Wika at Kultura','Ano ang "Sapir-Whorf hypothesis"?','Wika ay hindi nakakaapekto sa pag-iisip','Wika ay nakakaapekto sa paraan ng pag-iisip','Wika ay walang kaugnayan sa kultura','Wika ay universal',2);
ins($con,'filipino',8,'Wika at Kultura','Ano ang "linguistic relativity"?','Lahat ng wika ay magkapareho','Ang wika ay nakakaapekto sa pananaw ng mundo','Ang wika ay hindi nakakaapekto sa pananaw','Ang wika ay universal',2);
ins($con,'filipino',8,'Wika at Kultura','Ano ang "language revitalization"?','Pagkawala ng wika','Mga pagsisikap na muling buhayin ang nanganganib na wika','Paglikha ng bagong wika','Pagpapalawak ng wika',2);
ins($con,'filipino',8,'Wika at Kultura','Ano ang "ethnolinguistics"?','Pag-aaral ng gramatika','Pag-aaral ng relasyon ng wika at kultura','Pag-aaral ng kasaysayan ng wika','Pag-aaral ng talasalitaan',2);
ins($con,'filipino',8,'Wika at Kultura','Ano ang "language policy"?','Pag-aaral ng wika','Opisyal na desisyon ng gobyerno tungkol sa paggamit ng wika','Paglikha ng bagong wika','Pagpapalawak ng wika',2);
ins($con,'filipino',9,'Wika at Kultura','Ano ang "critical discourse analysis"?','Simpleng pagsusuri ng teksto','Pagsusuri ng relasyon ng wika, kapangyarihan, at ideolohiya','Pag-aaral ng gramatika','Pag-aaral ng talasalitaan',2);
ins($con,'filipino',9,'Wika at Kultura','Ano ang "performativity" (Butler)?','Katangiang hindi nagbabago','Ang pagkakakilanlan ay nabubuo sa pamamagitan ng paulit-ulit na gawi','Katangiang likas','Katangiang panlipunan',2);
ins($con,'filipino',9,'Wika at Kultura','Ano ang "third space" (Bhabha)?','Pisikal na lugar','Espasyong kultural na nabubuo sa pagtatagpo ng dalawang kultura','Ikatlong kultura','Ikatlong wika',2);
ins($con,'filipino',9,'Wika at Kultura','Ano ang "decolonization" ng wika?','Pagpapalawak ng kolonyal na wika','Pagpapalaya ng wika mula sa impluwensya ng kolonyalismo','Paglikha ng bagong wika','Pagpapalawak ng wika',2);
ins($con,'filipino',9,'Wika at Kultura','Ano ang "translanguaging"?','Paggamit ng iisang wika','Dinamikong paggamit ng lahat ng linggwistikong mapagkukunan ng isang tao','Pagtuturo ng wika','Pag-aaral ng wika',2);
ins($con,'filipino',10,'Wika at Kultura','Ano ang "linguistic anthropology"?','Pag-aaral ng gramatika','Pag-aaral ng wika bilang bahagi ng kultura at lipunan','Pag-aaral ng kasaysayan ng wika','Pag-aaral ng talasalitaan',2);
ins($con,'filipino',10,'Wika at Kultura','Ano ang "semiotics"?','Pag-aaral ng gramatika','Pag-aaral ng mga tanda at simbolo','Pag-aaral ng kasaysayan ng wika','Pag-aaral ng talasalitaan',2);
ins($con,'filipino',10,'Wika at Kultura','Ano ang "discourse community"?','Isang grupo ng mga kaibigan','Grupo ng mga taong nagbabahagi ng mga layunin at gawi sa komunikasyon','Isang paaralan','Isang organisasyon',2);
ins($con,'filipino',10,'Wika at Kultura','Ano ang "language ideology"?','Pag-aaral ng gramatika','Mga paniniwala at saloobin tungkol sa wika','Pag-aaral ng kasaysayan ng wika','Pag-aaral ng talasalitaan',2);
ins($con,'filipino',10,'Wika at Kultura','Ano ang "metalinguistics"?','Pag-aaral ng gramatika','Pag-aaral ng wika tungkol sa wika','Pag-aaral ng kasaysayan ng wika','Pag-aaral ng talasalitaan',2);

// ============================================================
// AP — Ekonomiks
// ============================================================
// Level 1
ins($con,'ap',1,'Ekonomiks','Ano ang "ekonomiks"?','Pag-aaral ng kasaysayan','Pag-aaral ng paggamit ng limitadong mapagkukunan','Pag-aaral ng pamahalaan','Pag-aaral ng heograpiya',2);
ins($con,'ap',1,'Ekonomiks','Ano ang "pangangailangan"?','Mga bagay na gusto lamang','Mga bagay na kailangan para mabuhay','Mga bagay na mahal','Mga bagay na bago',2);
ins($con,'ap',1,'Ekonomiks','Ano ang "kagustuhan"?','Mga bagay na kailangan','Mga bagay na gusto ngunit hindi kailangan','Mga bagay na libre','Mga bagay na lumang',2);
ins($con,'ap',1,'Ekonomiks','Ano ang "kakulangan"?','Labis na mapagkukunan','Limitadong mapagkukunan kumpara sa walang limitasyong pangangailangan','Maraming pera','Maraming pagkain',2);
ins($con,'ap',1,'Ekonomiks','Ano ang "palengke"?','Lugar ng pamahalaan','Lugar kung saan nagtatagpo ang mga mamimili at nagbebenta','Lugar ng paaralan','Lugar ng simbahan',2);
// Level 2
ins($con,'ap',2,'Ekonomiks','Ano ang "supply at demand"?','Pag-aaral ng kasaysayan','Batas ng ekonomiya tungkol sa dami ng produkto at presyo','Pag-aaral ng pamahalaan','Pag-aaral ng heograpiya',2);
ins($con,'ap',2,'Ekonomiks','Kapag tumaas ang presyo ng isang produkto, ano ang mangyayari sa demand?','Tataas','Bababa','Mananatiling pareho','Magiging zero',2);
ins($con,'ap',2,'Ekonomiks','Ano ang "inflation"?','Pagbaba ng presyo','Pagtaas ng pangkalahatang antas ng presyo','Pagbaba ng produksyon','Pagtaas ng trabaho',2);
ins($con,'ap',2,'Ekonomiks','Ano ang "GDP"?','Gross Domestic Product','General Domestic Product','Gross Demand Product','General Demand Product',1);
ins($con,'ap',2,'Ekonomiks','Ano ang "unemployment"?','Lahat ay may trabaho','Kondisyon kung saan ang mga taong naghahanap ng trabaho ay walang trabaho','Labis na trabaho','Mababang sahod',2);
// Level 3
ins($con,'ap',3,'Ekonomiks','Ano ang "oportunidad na gastos"?','Aktwal na gastos','Halaga ng pinakamahusay na alternatibong hindi pinili','Gastos sa produksyon','Gastos sa transportasyon',2);
ins($con,'ap',3,'Ekonomiks','Ano ang "elastisidad ng demand"?','Pagbabago ng supply','Pagiging sensitibo ng demand sa pagbabago ng presyo','Pagbabago ng produksyon','Pagbabago ng kita',2);
ins($con,'ap',3,'Ekonomiks','Ano ang "monopolyo"?','Maraming nagbebenta','Iisang nagbebenta sa isang merkado','Dalawang nagbebenta','Maraming mamimili',2);
ins($con,'ap',3,'Ekonomiks','Ano ang "oligopolyo"?','Iisang nagbebenta','Ilang nagbebenta na nagkokontrol sa merkado','Maraming nagbebenta','Walang nagbebenta',2);
ins($con,'ap',3,'Ekonomiks','Ano ang "kompetisyon"?','Walang nagbebenta','Pakikipagkumpitensya ng mga negosyo para sa mga mamimili','Iisang nagbebenta','Dalawang nagbebenta',2);
// Level 4
ins($con,'ap',4,'Ekonomiks','Ano ang "piskal na patakaran"?','Patakaran ng bangko sentral','Paggamit ng paggastos ng gobyerno at buwis upang impluwensyahan ang ekonomiya','Patakaran sa kalakalan','Patakaran sa trabaho',2);
ins($con,'ap',4,'Ekonomiks','Ano ang "monetary policy"?','Patakaran ng gobyerno sa paggastos','Patakaran ng bangko sentral sa supply ng pera','Patakaran sa kalakalan','Patakaran sa trabaho',2);
ins($con,'ap',4,'Ekonomiks','Ano ang "trade balance"?','Balanse ng pera','Pagkakaiba ng halaga ng exports at imports','Balanse ng badyet','Balanse ng utang',2);
ins($con,'ap',4,'Ekonomiks','Ano ang "comparative advantage"?','Kakayahang gumawa ng lahat','Kakayahang gumawa ng produkto sa mas mababang oportunidad na gastos','Kakayahang gumawa ng marami','Kakayahang gumawa ng mabilis',2);
ins($con,'ap',4,'Ekonomiks','Ano ang "proteksyonismo"?','Libreng kalakalan','Patakaran ng pamahalaan upang protektahan ang lokal na industriya','Patakaran sa trabaho','Patakaran sa pera',2);
// Level 5-10 (condensed)
ins($con,'ap',5,'Ekonomiks','Ano ang "Keynesian economics"?','Teorya ng libreng merkado','Teorya na ang pamahalaan ay dapat gumastos upang pasiglahin ang ekonomiya','Teorya ng supply-side','Teorya ng monetarismo',2);
ins($con,'ap',5,'Ekonomiks','Ano ang "supply-side economics"?','Teorya ng demand','Teorya na ang pagbabawas ng buwis ay magpapalakas ng ekonomiya','Teorya ng Keynes','Teorya ng monetarismo',2);
ins($con,'ap',5,'Ekonomiks','Ano ang "free trade"?','Kalakalan na may taripa','Kalakalan nang walang hadlang o taripa','Kalakalan sa loob ng bansa','Kalakalan sa pagitan ng mga lalawigan',2);
ins($con,'ap',5,'Ekonomiks','Ano ang "WTO"?','World Trade Organization','World Tourism Organization','World Technology Organization','World Transport Organization',1);
ins($con,'ap',5,'Ekonomiks','Ano ang "IMF"?','International Monetary Fund','International Market Fund','International Management Fund','International Manufacturing Fund',1);
ins($con,'ap',6,'Ekonomiks','Ano ang "recession"?','Paglago ng ekonomiya','Pagbaba ng aktibidad ng ekonomiya sa loob ng dalawang magkakasunod na quarter','Pagtaas ng inflation','Pagtaas ng trabaho',2);
ins($con,'ap',6,'Ekonomiks','Ano ang "depression"?','Maikling recession','Matagal at malubhang pagbaba ng aktibidad ng ekonomiya','Pagtaas ng inflation','Pagtaas ng trabaho',2);
ins($con,'ap',6,'Ekonomiks','Ano ang "stagflation"?','Mataas na paglago at mababang inflation','Mataas na inflation at mataas na unemployment','Mababang inflation at mababang unemployment','Mataas na paglago at mataas na trabaho',2);
ins($con,'ap',6,'Ekonomiks','Ano ang "human development index" (HDI)?','Sukatan ng GDP lamang','Sukatan ng pag-unlad ng tao batay sa kita, edukasyon, at kalusugan','Sukatan ng inflation','Sukatan ng trabaho',2);
ins($con,'ap',6,'Ekonomiks','Ano ang "Gini coefficient"?','Sukatan ng GDP','Sukatan ng kawalan ng pagkakapantay-pantay ng kita','Sukatan ng inflation','Sukatan ng trabaho',2);
ins($con,'ap',7,'Ekonomiks','Ano ang "externality"?','Direktang gastos','Epekto ng aktibidad ng ekonomiya sa mga hindi kasangkot','Gastos sa produksyon','Gastos sa transportasyon',2);
ins($con,'ap',7,'Ekonomiks','Ano ang "public goods"?','Mga produktong pribado','Mga produktong hindi eksklusibo at hindi mapagkumpitensya','Mga produktong mahal','Mga produktong libre',2);
ins($con,'ap',7,'Ekonomiks','Ano ang "market failure"?','Tagumpay ng merkado','Kondisyon kung saan ang merkado ay hindi epektibong naglalaan ng mapagkukunan','Pagbaba ng presyo','Pagtaas ng supply',2);
ins($con,'ap',7,'Ekonomiks','Ano ang "asymmetric information"?','Pantay na impormasyon','Kondisyon kung saan ang isang partido ay may mas maraming impormasyon','Walang impormasyon','Labis na impormasyon',2);
ins($con,'ap',7,'Ekonomiks','Ano ang "moral hazard"?','Etikal na pag-uugali','Tendensyang kumuha ng mas maraming panganib kapag protektado','Mababang panganib','Walang panganib',2);
ins($con,'ap',8,'Ekonomiks','Ano ang "game theory"?','Teorya ng laro','Pag-aaral ng estratehikong desisyon sa pagitan ng mga kalahok','Teorya ng ekonomiya','Teorya ng pamahalaan',2);
ins($con,'ap',8,'Ekonomiks','Ano ang "Nash equilibrium"?','Pinakamataas na kita','Kondisyon kung saan walang manlalaro ang may insentibo na baguhin ang estratehiya','Pinakamababang gastos','Pinakamataas na supply',2);
ins($con,'ap',8,'Ekonomiks','Ano ang "behavioral economics"?','Tradisyonal na ekonomiya','Pag-aaral ng epekto ng sikolohikal na salik sa desisyon ng ekonomiya','Pag-aaral ng pamahalaan','Pag-aaral ng kasaysayan',2);
ins($con,'ap',8,'Ekonomiks','Ano ang "nudge theory"?','Pagpilit sa mga tao','Pagdisenyo ng pagpipilian upang gabayan ang mga tao sa mas mabuting desisyon','Pagbabago ng batas','Pagbabago ng presyo',2);
ins($con,'ap',8,'Ekonomiks','Ano ang "circular economy"?','Linear na ekonomiya','Sistemang naglalayong mabawasan ang basura sa pamamagitan ng muling paggamit','Tradisyonal na ekonomiya','Modernong ekonomiya',2);
ins($con,'ap',9,'Ekonomiks','Ano ang "sustainable development"?','Mabilis na pag-unlad','Pag-unlad na nakakatugon sa pangangailangan ngayon nang hindi sinisira ang kakayahan ng hinaharap','Pag-unlad ng industriya','Pag-unlad ng teknolohiya',2);
ins($con,'ap',9,'Ekonomiks','Ano ang "SDGs"?','Sustainable Development Goals','Social Development Goals','Scientific Development Goals','Standard Development Goals',1);
ins($con,'ap',9,'Ekonomiks','Ano ang "carbon tax"?','Buwis sa lahat ng produkto','Buwis sa mga nagtatangkilik ng carbon emissions','Buwis sa transportasyon','Buwis sa industriya',2);
ins($con,'ap',9,'Ekonomiks','Ano ang "green economy"?','Ekonomiyang may maraming puno','Ekonomiyang naglalayong mabawasan ang epekto sa kapaligiran','Ekonomiyang pang-agrikultura','Ekonomiyang pang-industriya',2);
ins($con,'ap',9,'Ekonomiks','Ano ang "digital economy"?','Ekonomiyang walang teknolohiya','Ekonomiyang batay sa digital na teknolohiya','Ekonomiyang pang-agrikultura','Ekonomiyang pang-industriya',2);
ins($con,'ap',10,'Ekonomiks','Ano ang "cryptocurrency"?','Tradisyonal na pera','Digital na pera na gumagamit ng cryptography','Pera ng gobyerno','Pera ng bangko',2);
ins($con,'ap',10,'Ekonomiks','Ano ang "blockchain"?','Uri ng pera','Desentralisadong ledger na nagtatala ng mga transaksyon','Uri ng bangko','Uri ng gobyerno',2);
ins($con,'ap',10,'Ekonomiks','Ano ang "gig economy"?','Tradisyonal na trabaho','Ekonomiyang batay sa pansamantalang trabaho at freelance','Ekonomiyang pang-agrikultura','Ekonomiyang pang-industriya',2);
ins($con,'ap',10,'Ekonomiks','Ano ang "universal basic income"?','Buwis sa lahat','Regular na pagbabayad ng gobyerno sa lahat ng mamamayan','Sahod ng gobyerno','Tulong sa mahirap lamang',2);
ins($con,'ap',10,'Ekonomiks','Ano ang "degrowth"?','Mabilis na paglago','Kilusang nagtataguyod ng pagbabawas ng produksyon at konsumo para sa kapaligiran','Tradisyonal na ekonomiya','Modernong ekonomiya',2);

// ============================================================
// AP — Kasaysayan ng Pilipinas
// ============================================================
// Level 1
ins($con,'ap',1,'Kasaysayan ng Pilipinas','Sino ang nagtatag ng Katipunan?','Jose Rizal','Andres Bonifacio','Emilio Aguinaldo','Antonio Luna',2);
ins($con,'ap',1,'Kasaysayan ng Pilipinas','Kailan natuklasan ng mga Espanyol ang Pilipinas?','1521','1565','1898','1946',1);
ins($con,'ap',1,'Kasaysayan ng Pilipinas','Sino ang unang Pangulo ng Pilipinas?','Jose Rizal','Andres Bonifacio','Emilio Aguinaldo','Manuel Quezon',3);
ins($con,'ap',1,'Kasaysayan ng Pilipinas','Kailan nakamit ng Pilipinas ang kalayaan mula sa Amerika?','1898','1935','1946','1972',3);
ins($con,'ap',1,'Kasaysayan ng Pilipinas','Sino ang sumulat ng Noli Me Tangere?','Andres Bonifacio','Emilio Aguinaldo','Jose Rizal','Antonio Luna',3);
// Level 2
ins($con,'ap',2,'Kasaysayan ng Pilipinas','Ano ang "Katipunan"?','Isang partido politikal','Isang rebolusyonaryong organisasyon laban sa Espanyol','Isang relihiyosong grupo','Isang pangkat ng mga manunulat',2);
ins($con,'ap',2,'Kasaysayan ng Pilipinas','Ano ang "Cry of Pugad Lawin"?','Pagdating ng mga Espanyol','Simula ng Rebolusyong Pilipino noong 1896','Pagdating ng mga Amerikano','Pagkamit ng kalayaan',2);
ins($con,'ap',2,'Kasaysayan ng Pilipinas','Sino si Ferdinand Magellan?','Pilipinong bayani','Portuges na manlalayag na natuklasan ang Pilipinas para sa Espanya','Amerikanong heneral','Espanyol na gobernador',2);
ins($con,'ap',2,'Kasaysayan ng Pilipinas','Ano ang "Galleon Trade"?','Kalakalan sa loob ng Pilipinas','Kalakalan sa pagitan ng Pilipinas at Mexico sa pamamagitan ng mga galleon','Kalakalan sa pagitan ng Pilipinas at Espanya','Kalakalan sa pagitan ng Pilipinas at Tsina',2);
ins($con,'ap',2,'Kasaysayan ng Pilipinas','Sino si Lapu-Lapu?','Espanyol na manlalayag','Pilipinong datu na pumatay kay Magellan','Pilipinong bayani ng Rebolusyon','Amerikanong heneral',2);
// Level 3
ins($con,'ap',3,'Kasaysayan ng Pilipinas','Ano ang "Malolos Constitution"?','Konstitusyon ng Pilipinas sa ilalim ng Amerika','Unang konstitusyon ng Pilipinas noong 1899','Konstitusyon ng Pilipinas noong 1935','Konstitusyon ng Pilipinas noong 1987',2);
ins($con,'ap',3,'Kasaysayan ng Pilipinas','Ano ang "Philippine-American War"?','Digmaan laban sa Espanya','Digmaan ng Pilipinas laban sa Amerika matapos ang 1898','Digmaan sa loob ng Pilipinas','Digmaan laban sa Hapon',2);
ins($con,'ap',3,'Kasaysayan ng Pilipinas','Ano ang "Commonwealth Period"?','Panahon ng Espanyol','Panahon ng paghahanda para sa kalayaan sa ilalim ng Amerika (1935-1946)','Panahon ng Hapon','Panahon pagkatapos ng kalayaan',2);
ins($con,'ap',3,'Kasaysayan ng Pilipinas','Sino si Manuel L. Quezon?','Unang Pangulo ng Pilipinas','Pangulo ng Commonwealth ng Pilipinas','Pangulo sa panahon ng Hapon','Pangulo pagkatapos ng kalayaan',2);
ins($con,'ap',3,'Kasaysayan ng Pilipinas','Ano ang "Bataan Death March"?','Martsa ng tagumpay','Pinilit na martsa ng mga bilangong Pilipino at Amerikano sa ilalim ng Hapon','Martsa ng kalayaan','Martsa ng rebolusyon',2);
// Level 4
ins($con,'ap',4,'Kasaysayan ng Pilipinas','Ano ang "Martial Law" sa Pilipinas?','Demokratikong panahon','Panahon ng diktadura ni Ferdinand Marcos (1972-1986)','Panahon ng Hapon','Panahon ng Amerika',2);
ins($con,'ap',4,'Kasaysayan ng Pilipinas','Ano ang "EDSA Revolution"?','Rebolusyon laban sa Espanya','Mapayapang rebolusyon noong 1986 na nagpaalis kay Marcos','Rebolusyon laban sa Amerika','Rebolusyon laban sa Hapon',2);
ins($con,'ap',4,'Kasaysayan ng Pilipinas','Sino si Corazon Aquino?','Unang babaeng Pangulo ng Pilipinas','Pangulo sa panahon ng Hapon','Pangulo sa panahon ng Amerika','Pangulo sa panahon ng Espanya',1);
ins($con,'ap',4,'Kasaysayan ng Pilipinas','Ano ang "Hukbalahap"?','Rebolusyonaryong grupo laban sa Espanya','Kilusang gerilya laban sa Hapon sa panahon ng Ikalawang Digmaang Pandaigdig','Kilusang laban sa Amerika','Kilusang laban sa Marcos',2);
ins($con,'ap',4,'Kasaysayan ng Pilipinas','Ano ang "Treaty of Paris" (1898)?','Kasunduan sa pagitan ng Pilipinas at Espanya','Kasunduan kung saan ibinenta ng Espanya ang Pilipinas sa Amerika','Kasunduan sa pagitan ng Pilipinas at Amerika','Kasunduan sa pagitan ng Pilipinas at Hapon',2);
// Level 5-10 (condensed)
ins($con,'ap',5,'Kasaysayan ng Pilipinas','Ano ang "Propaganda Movement"?','Kilusang rebolusyonaryo','Kilusang nagtataguyod ng reporma sa pamamagitan ng mapayapang paraan','Kilusang laban sa Amerika','Kilusang laban sa Hapon',2);
ins($con,'ap',5,'Kasaysayan ng Pilipinas','Sino si Marcelo H. del Pilar?','Bayani ng Rebolusyon','Isa sa mga lider ng Propaganda Movement','Pangulo ng Pilipinas','Heneral ng Katipunan',2);
ins($con,'ap',5,'Kasaysayan ng Pilipinas','Ano ang "La Solidaridad"?','Pahayagan ng Katipunan','Pahayagan ng Propaganda Movement sa Espanya','Pahayagan ng Rebolusyon','Pahayagan ng pamahalaan',2);
ins($con,'ap',5,'Kasaysayan ng Pilipinas','Ano ang "Pact of Biak-na-Bato"?','Kasunduan sa pagitan ng Pilipinas at Amerika','Kasunduan sa pagitan ng Katipunan at Espanya','Kasunduan sa pagitan ng Pilipinas at Hapon','Kasunduan sa pagitan ng mga Pilipino',2);
ins($con,'ap',5,'Kasaysayan ng Pilipinas','Sino si Apolinario Mabini?','Bayani ng Rebolusyon na kilala bilang "Utak ng Rebolusyon"','Pangulo ng Pilipinas','Heneral ng Katipunan','Lider ng Propaganda Movement',1);
ins($con,'ap',6,'Kasaysayan ng Pilipinas','Ano ang "Tejeros Convention"?','Pagtitipon ng mga Espanyol','Pagtitipon kung saan pinalitan si Bonifacio ng pamunuan ng Katipunan','Pagtitipon ng mga Amerikano','Pagtitipon ng mga Hapon',2);
ins($con,'ap',6,'Kasaysayan ng Pilipinas','Ano ang "Aguinaldo-Bonifacio conflict"?','Pakikipagtulungan','Hidwaan sa pagitan ng dalawang lider ng Rebolusyon','Pakikipaglaban sa Espanya','Pakikipaglaban sa Amerika',2);
ins($con,'ap',6,'Kasaysayan ng Pilipinas','Ano ang "Balangiga Massacre"?','Masaker ng mga Pilipino sa mga Espanyol','Masaker ng mga Pilipino sa mga Amerikanong sundalo sa Samar','Masaker ng mga Hapon sa mga Pilipino','Masaker ng mga Pilipino sa mga Hapon',2);
ins($con,'ap',6,'Kasaysayan ng Pilipinas','Ano ang "Jones Act" (1916)?','Batas na nagbibigay ng kalayaan sa Pilipinas','Batas na nangako ng kalayaan sa Pilipinas sa hinaharap','Batas na nagtatayo ng Commonwealth','Batas na nagbibigay ng karapatan sa pagboto',2);
ins($con,'ap',6,'Kasaysayan ng Pilipinas','Ano ang "Tydings-McDuffie Act" (1934)?','Batas na nagbibigay ng kalayaan agad','Batas na nagtayo ng Commonwealth at nangako ng kalayaan pagkatapos ng 10 taon','Batas na nagtatayo ng gobyerno','Batas na nagbibigay ng karapatan sa pagboto',2);
ins($con,'ap',7,'Kasaysayan ng Pilipinas','Ano ang "Hare-Hawes-Cutting Act"?','Batas na tinanggap ng Pilipinas','Batas na tinanggihan ng Pilipinas na nagtatayo ng Commonwealth','Batas na nagbibigay ng kalayaan agad','Batas na nagtatayo ng gobyerno',2);
ins($con,'ap',7,'Kasaysayan ng Pilipinas','Ano ang "Bell Trade Act"?','Kasunduan sa kalayaan','Kasunduan sa kalakalan sa pagitan ng Pilipinas at Amerika pagkatapos ng kalayaan','Kasunduan sa depensa','Kasunduan sa edukasyon',2);
ins($con,'ap',7,'Kasaysayan ng Pilipinas','Ano ang "Laurel-Langley Agreement"?','Kasunduan sa depensa','Kasunduan sa kalakalan na nagbago ng Bell Trade Act','Kasunduan sa edukasyon','Kasunduan sa kultura',2);
ins($con,'ap',7,'Kasaysayan ng Pilipinas','Ano ang "Marcos era" sa kasaysayan ng Pilipinas?','Panahon ng demokrasya','Panahon ng diktadura at martial law (1965-1986)','Panahon ng Commonwealth','Panahon ng Hapon',2);
ins($con,'ap',7,'Kasaysayan ng Pilipinas','Ano ang "People Power Revolution"?','Rebolusyong armado','Mapayapang rebolusyon ng mamamayan noong 1986','Rebolusyon laban sa Hapon','Rebolusyon laban sa Amerika',2);
ins($con,'ap',8,'Kasaysayan ng Pilipinas','Ano ang "Cory Aquino administration"?','Panahon ng diktadura','Panahon ng pagpapanumbalik ng demokrasya pagkatapos ng EDSA Revolution','Panahon ng Commonwealth','Panahon ng Hapon',2);
ins($con,'ap',8,'Kasaysayan ng Pilipinas','Ano ang "1987 Constitution"?','Konstitusyon sa panahon ng Marcos','Konstitusyon na isinulat pagkatapos ng EDSA Revolution','Konstitusyon ng Commonwealth','Konstitusyon ng Malolos',2);
ins($con,'ap',8,'Kasaysayan ng Pilipinas','Ano ang "Ramos administration"?','Panahon ng diktadura','Panahon ng ekonomikong paglago at kapayapaan (1992-1998)','Panahon ng Commonwealth','Panahon ng Hapon',2);
ins($con,'ap',8,'Kasaysayan ng Pilipinas','Ano ang "Estrada impeachment"?','Tagumpay ng impeachment','Proseso ng impeachment laban kay Pangulong Estrada na humantong sa EDSA II','Tagumpay ng gobyerno','Tagumpay ng ekonomiya',2);
ins($con,'ap',8,'Kasaysayan ng Pilipinas','Ano ang "EDSA II"?','Unang EDSA Revolution','Ikalawang pagtitipon sa EDSA na nagpaalis kay Estrada','Ikatlong EDSA Revolution','Ikaapat na EDSA Revolution',2);
ins($con,'ap',9,'Kasaysayan ng Pilipinas','Ano ang "Bangsamoro"?','Pangalan ng isang lalawigan','Rehiyong Muslim sa Mindanao na may sariling pamahalaan','Pangalan ng isang lungsod','Pangalan ng isang isla',2);
ins($con,'ap',9,'Kasaysayan ng Pilipinas','Ano ang "Comprehensive Agreement on the Bangsamoro"?','Kasunduan sa kalakalan','Kasunduan sa kapayapaan sa pagitan ng gobyerno at MILF','Kasunduan sa depensa','Kasunduan sa edukasyon',2);
ins($con,'ap',9,'Kasaysayan ng Pilipinas','Ano ang "Marawi siege" (2017)?','Lindol sa Marawi','Labanan sa pagitan ng militar at ISIS-linked na grupo sa Marawi','Bagyo sa Marawi','Sunog sa Marawi',2);
ins($con,'ap',9,'Kasaysayan ng Pilipinas','Ano ang "Duterte administration"?','Panahon ng demokrasya','Panahon ng "war on drugs" at pagbabago ng patakaran (2016-2022)','Panahon ng Commonwealth','Panahon ng Hapon',2);
ins($con,'ap',9,'Kasaysayan ng Pilipinas','Ano ang "South China Sea dispute"?','Kasunduan sa kalakalan','Hidwaan sa pagitan ng Pilipinas at Tsina sa West Philippine Sea','Kasunduan sa depensa','Kasunduan sa edukasyon',2);
ins($con,'ap',10,'Kasaysayan ng Pilipinas','Ano ang "Marcos Jr. administration"?','Panahon ng diktadura','Administrasyon ni Bongbong Marcos na nagsimula noong 2022','Panahon ng Commonwealth','Panahon ng Hapon',2);
ins($con,'ap',10,'Kasaysayan ng Pilipinas','Ano ang "historical revisionism"?','Tapat na pagsusuri ng kasaysayan','Pagbabago ng interpretasyon ng kasaysayan upang maglingkod sa isang agenda','Pag-aaral ng kasaysayan','Pagtuturo ng kasaysayan',2);
ins($con,'ap',10,'Kasaysayan ng Pilipinas','Ano ang "decolonization" ng kasaysayan ng Pilipinas?','Pagpapalawak ng kolonyal na kasaysayan','Pagpapalaya ng kasaysayan mula sa kolonyal na pananaw','Paglikha ng bagong kasaysayan','Pagpapalawak ng kasaysayan',2);
ins($con,'ap',10,'Kasaysayan ng Pilipinas','Ano ang "oral history"?','Nakasulat na kasaysayan','Kasaysayang naipapasa sa pamamagitan ng salita','Modernong kasaysayan','Banyagang kasaysayan',2);
ins($con,'ap',10,'Kasaysayan ng Pilipinas','Ano ang "primary source" sa kasaysayan?','Pangalawang pinagkukunan','Orihinal na dokumento o artefakto mula sa panahon na pinag-aaralan','Modernong interpretasyon','Pangalawang interpretasyon',2);

// ============================================================
// AP — Kontemporaryong Isyu
// ============================================================
// Level 1
ins($con,'ap',1,'Kontemporaryong Isyu','Ano ang "climate change"?','Pagbabago ng panahon sa isang araw','Pangmatagalang pagbabago ng temperatura at klima ng Mundo','Pagbabago ng kultura','Pagbabago ng ekonomiya',2);
ins($con,'ap',1,'Kontemporaryong Isyu','Ano ang "kalikasan"?','Mga gusali at kalsada','Ang kapaligiran at lahat ng nabubuhay na bagay','Mga makina at teknolohiya','Mga tao at lipunan',2);
ins($con,'ap',1,'Kontemporaryong Isyu','Ano ang "polusyon"?','Malinis na kapaligiran','Pagdumi ng hangin, tubig, o lupa','Pagpapalinis ng kapaligiran','Pagpapalaki ng kalikasan',2);
ins($con,'ap',1,'Kontemporaryong Isyu','Ano ang "kahirapan"?','Pagiging mayaman','Kondisyon ng kakulangan ng pangunahing pangangailangan','Pagiging masaya','Pagiging malusog',2);
ins($con,'ap',1,'Kontemporaryong Isyu','Ano ang "edukasyon"?','Pag-aaral ng isang paksa lamang','Proseso ng pagkatuto at pagpapaunlad ng kaalaman at kasanayan','Pag-aaral ng kasaysayan lamang','Pag-aaral ng matematika lamang',2);
// Level 2
ins($con,'ap',2,'Kontemporaryong Isyu','Ano ang "globalisasyon"?','Pagpapalaki ng isang bansa','Proseso ng pagsasama ng mga ekonomiya, kultura, at lipunan sa buong mundo','Pagpapalaki ng isang lungsod','Pagpapalaki ng isang komunidad',2);
ins($con,'ap',2,'Kontemporaryong Isyu','Ano ang "human rights"?','Karapatan ng mga mayayaman lamang','Mga karapatang likas na pag-aari ng bawat tao','Karapatan ng mga matatanda lamang','Karapatan ng mga lalaki lamang',2);
ins($con,'ap',2,'Kontemporaryong Isyu','Ano ang "gender equality"?','Pagkakapareho ng lahat ng tao','Pagkakapantay-pantay ng mga kababaihan at kalalakihan','Pagkakapareho ng lahat ng kultura','Pagkakapareho ng lahat ng relihiyon',2);
ins($con,'ap',2,'Kontemporaryong Isyu','Ano ang "terrorism"?','Pakikipaglaban sa digmaan','Paggamit ng karahasan upang maghasik ng takot para sa pulitikal na layunin','Pakikipaglaban sa krimen','Pakikipaglaban sa kahirapan',2);
ins($con,'ap',2,'Kontemporaryong Isyu','Ano ang "migration"?','Pagbabago ng trabaho','Paglipat ng mga tao mula sa isang lugar patungo sa isa pa','Pagbabago ng kultura','Pagbabago ng relihiyon',2);
// Level 3
ins($con,'ap',3,'Kontemporaryong Isyu','Ano ang "sustainable development goals" (SDGs)?','Mga layunin ng isang bansa','17 pandaigdigang layunin ng UN para sa mas mabuting mundo','Mga layunin ng isang kumpanya','Mga layunin ng isang organisasyon',2);
ins($con,'ap',3,'Kontemporaryong Isyu','Ano ang "cybercrime"?','Krimen sa kalye','Krimen na ginagawa sa pamamagitan ng internet o teknolohiya','Krimen sa opisina','Krimen sa paaralan',2);
ins($con,'ap',3,'Kontemporaryong Isyu','Ano ang "fake news"?','Totoong balita','Maling impormasyon na ipinamamahagi bilang totoo','Lumang balita','Bagong balita',2);
ins($con,'ap',3,'Kontemporaryong Isyu','Ano ang "social media"?','Tradisyonal na media','Mga online na platform para sa pakikipag-ugnayan at pagbabahagi ng impormasyon','Pahayagan','Telebisyon',2);
ins($con,'ap',3,'Kontemporaryong Isyu','Ano ang "drug addiction"?','Malusog na gawi','Pag-asa sa droga na nagdudulot ng pisikal at sikolohikal na pinsala','Maayos na pamumuhay','Malusog na pamumuhay',2);
// Level 4
ins($con,'ap',4,'Kontemporaryong Isyu','Ano ang "OFW"?','Overseas Filipino Worker','Official Filipino Worker','Organized Filipino Worker','Outstanding Filipino Worker',1);
ins($con,'ap',4,'Kontemporaryong Isyu','Ano ang "brain drain"?','Pagpapalakas ng utak','Paglipat ng mga may kakayahan at edukadong tao sa ibang bansa','Pagpapalakas ng edukasyon','Pagpapalakas ng ekonomiya',2);
ins($con,'ap',4,'Kontemporaryong Isyu','Ano ang "corruption"?','Mabuting pamamahala','Pag-abuso ng kapangyarihan para sa personal na kapakinabangan','Mabuting serbisyo','Mabuting ekonomiya',2);
ins($con,'ap',4,'Kontemporaryong Isyu','Ano ang "environmental degradation"?','Pagpapabuti ng kapaligiran','Pagkasira ng kapaligiran dahil sa aktibidad ng tao','Pagpapalaki ng kalikasan','Pagpapalinis ng kapaligiran',2);
ins($con,'ap',4,'Kontemporaryong Isyu','Ano ang "food security"?','Seguridad ng pagkain mula sa mga magnanakaw','Kondisyon kung saan ang lahat ay may access sa sapat na pagkain','Seguridad ng mga restawran','Seguridad ng mga palengke',2);
// Level 5
ins($con,'ap',5,'Kontemporaryong Isyu','Ano ang "pandemic"?','Lokal na sakit','Sakit na kumakalat sa buong mundo','Sakit ng isang bansa','Sakit ng isang lungsod',2);
ins($con,'ap',5,'Kontemporaryong Isyu','Ano ang "vaccine hesitancy"?','Pagnanais na magpabakuna','Pag-aalangan o pagtanggi sa bakuna','Pagpapabakuna ng lahat','Paglikha ng bakuna',2);
ins($con,'ap',5,'Kontemporaryong Isyu','Ano ang "digital divide"?','Pagkakaiba ng mga digital na produkto','Agwat sa pagitan ng mga may access at walang access sa teknolohiya','Pagkakaiba ng mga social media','Pagkakaiba ng mga smartphone',2);
ins($con,'ap',5,'Kontemporaryong Isyu','Ano ang "income inequality"?','Pagkakapantay-pantay ng kita','Malaking agwat sa pagitan ng kita ng mayayaman at mahihirap','Pagkakapareho ng trabaho','Pagkakapareho ng edukasyon',2);
ins($con,'ap',5,'Kontemporaryong Isyu','Ano ang "mental health"?','Kalusugan ng katawan lamang','Kalusugan ng isip at emosyon','Kalusugan ng puso','Kalusugan ng utak',2);
// Level 6
ins($con,'ap',6,'Kontemporaryong Isyu','Ano ang "LGBTQ+ rights"?','Karapatan ng mga matatanda','Karapatan ng mga lesbian, gay, bisexual, transgender, at queer na tao','Karapatan ng mga bata','Karapatan ng mga manggagawa',2);
ins($con,'ap',6,'Kontemporaryong Isyu','Ano ang "indigenous peoples rights"?','Karapatan ng mga dayuhan','Karapatan ng mga katutubong mamamayan','Karapatan ng mga matatanda','Karapatan ng mga bata',2);
ins($con,'ap',6,'Kontemporaryong Isyu','Ano ang "press freedom"?','Kalayaan ng mga printer','Kalayaan ng media na mag-ulat nang walang censorship','Kalayaan ng mga mamamahayag na maglakbay','Kalayaan ng mga pahayagan na magbenta',2);
ins($con,'ap',6,'Kontemporaryong Isyu','Ano ang "nuclear proliferation"?','Pagbabawas ng nuclear weapons','Pagkalat ng nuclear weapons sa mas maraming bansa','Paggamit ng nuclear energy','Paglikha ng nuclear energy',2);
ins($con,'ap',6,'Kontemporaryong Isyu','Ano ang "refugee crisis"?','Krisis ng mga turista','Malaking bilang ng mga taong lumikas mula sa kanilang bansa dahil sa digmaan o pag-uusig','Krisis ng mga manggagawa','Krisis ng mga estudyante',2);
// Level 7
ins($con,'ap',7,'Kontemporaryong Isyu','Ano ang "geopolitics"?','Pag-aaral ng heograpiya lamang','Pag-aaral ng relasyon ng heograpiya at pulitikal na kapangyarihan','Pag-aaral ng ekonomiya','Pag-aaral ng kultura',2);
ins($con,'ap',7,'Kontemporaryong Isyu','Ano ang "multilateralism"?','Pakikipagtulungan ng dalawang bansa','Pakikipagtulungan ng maraming bansa sa iisang isyu','Pakikipagtulungan ng isang bansa','Pakikipagtulungan ng mga organisasyon',2);
ins($con,'ap',7,'Kontemporaryong Isyu','Ano ang "soft power"?','Pulitikal na kapangyarihan','Kakayahang impluwensyahan ang iba sa pamamagitan ng kultura at diplomasya','Militar na kapangyarihan','Ekonomikong kapangyarihan',2);
ins($con,'ap',7,'Kontemporaryong Isyu','Ano ang "populism"?','Elitistang pulitika','Pulitikal na kilusang nagtataguyod ng interes ng "ordinaryong tao" laban sa "elite"','Demokratikong pulitika','Sosyalistang pulitika',2);
ins($con,'ap',7,'Kontemporaryong Isyu','Ano ang "disinformation"?','Totoong impormasyon','Sadyang maling impormasyon na ipinamamahagi upang manlinlang','Lumang impormasyon','Bagong impormasyon',2);
// Level 8
ins($con,'ap',8,'Kontemporaryong Isyu','Ano ang "artificial intelligence" sa lipunan?','Teknolohiyang walang epekto','Teknolohiyang nagbabago ng trabaho, edukasyon, at lipunan','Teknolohiyang para sa mga siyentipiko lamang','Teknolohiyang para sa mga negosyo lamang',2);
ins($con,'ap',8,'Kontemporaryong Isyu','Ano ang "surveillance capitalism"?','Tradisyonal na kapitalismo','Modelo ng negosyo na kumikita mula sa pagkolekta at pagbebenta ng datos ng mga tao','Sosyalistang ekonomiya','Komunistang ekonomiya',2);
ins($con,'ap',8,'Kontemporaryong Isyu','Ano ang "post-truth"?','Panahon ng katotohanan','Panahon kung saan ang emosyon ay mas mahalaga kaysa sa katotohanan sa pulitika','Panahon ng siyensya','Panahon ng relihiyon',2);
ins($con,'ap',8,'Kontemporaryong Isyu','Ano ang "cancel culture"?','Pagsuporta sa mga tao','Praktis ng pagbubukod sa mga taong nagkamali sa pamamagitan ng social media','Pagpapatawad sa mga tao','Pagtulong sa mga tao',2);
ins($con,'ap',8,'Kontemporaryong Isyu','Ano ang "echo chamber"?','Lugar na may malakas na tunog','Sitwasyon kung saan ang mga tao ay nakakarinig lamang ng mga pananaw na katulad ng kanila','Lugar ng talakayan','Lugar ng debate',2);
// Level 9
ins($con,'ap',9,'Kontemporaryong Isyu','Ano ang "deglobalization"?','Pagpapalawak ng globalisasyon','Tendensyang bawasan ang pandaigdigang integrasyon','Pagpapalaki ng kalakalan','Pagpapalaki ng kultura',2);
ins($con,'ap',9,'Kontemporaryong Isyu','Ano ang "techno-nationalism"?','Pagmamahal sa teknolohiya','Paggamit ng teknolohiya bilang instrumento ng pambansang kapangyarihan','Pagmamahal sa bansa','Pagmamahal sa siyensya',2);
ins($con,'ap',9,'Kontemporaryong Isyu','Ano ang "climate justice"?','Katarungan para sa kalikasan lamang','Pagtugon sa hindi pantay na epekto ng climate change sa mga mahihirap na komunidad','Katarungan para sa mga mayayaman','Katarungan para sa mga bansa',2);
ins($con,'ap',9,'Kontemporaryong Isyu','Ano ang "intersectionality"?','Pagtatagpo ng mga kalsada','Pagsasama ng iba\'t ibang aspeto ng pagkakakilanlan na nakakaapekto sa karanasan ng diskriminasyon','Pagtatagpo ng mga kultura','Pagtatagpo ng mga relihiyon',2);
ins($con,'ap',9,'Kontemporaryong Isyu','Ano ang "epistemic injustice"?','Katarungan sa kaalaman','Kawalan ng katarungan sa pagkilala at pagpapahalaga ng kaalaman ng mga marginalisado','Katarungan sa edukasyon','Katarungan sa siyensya',2);
// Level 10
ins($con,'ap',10,'Kontemporaryong Isyu','Ano ang "Anthropocene"?','Panahon ng mga dinosaur','Kasalukuyang panahon kung saan ang tao ang pangunahing puwersa ng pagbabago ng Mundo','Panahon ng mga hayop','Panahon ng mga halaman',2);
ins($con,'ap',10,'Kontemporaryong Isyu','Ano ang "planetary boundaries"?','Hangganan ng mga planeta','Mga limitasyon ng sistema ng Mundo na dapat hindi lampasan upang mapanatili ang katatagan','Hangganan ng mga bansa','Hangganan ng mga kontinente',2);
ins($con,'ap',10,'Kontemporaryong Isyu','Ano ang "cosmopolitanism"?','Pagmamahal sa isang bansa lamang','Pananaw na ang lahat ng tao ay miyembro ng iisang pandaigdigang komunidad','Pagmamahal sa isang kultura lamang','Pagmamahal sa isang relihiyon lamang',2);
ins($con,'ap',10,'Kontemporaryong Isyu','Ano ang "biopolitics" (Foucault)?','Pulitika ng mga hayop','Paggamit ng kapangyarihan upang kontrolin ang buhay ng mga tao','Pulitika ng kalikasan','Pulitika ng teknolohiya',2);
ins($con,'ap',10,'Kontemporaryong Isyu','Ano ang "necropolitics" (Mbembe)?','Pulitika ng buhay','Kapangyarihang magpasya kung sino ang mabubuhay at mamamatay','Pulitika ng kalikasan','Pulitika ng teknolohiya',2);

// ============================================================
// AP — Heograpiya
// ============================================================
// Level 1
ins($con,'ap',1,'Heograpiya','Ano ang "heograpiya"?','Pag-aaral ng kasaysayan','Pag-aaral ng Mundo, mga lugar, at relasyon ng tao at kapaligiran','Pag-aaral ng ekonomiya','Pag-aaral ng kultura',2);
ins($con,'ap',1,'Heograpiya','Ilang isla ang mayroon ang Pilipinas?','5,000','6,000','7,641','8,000',3);
ins($con,'ap',1,'Heograpiya','Ano ang kabisera ng Pilipinas?','Cebu','Davao','Manila','Quezon City',3);
ins($con,'ap',1,'Heograpiya','Ano ang pinakamataas na bundok sa Pilipinas?','Mt. Mayon','Mt. Pinatubo','Mt. Apo','Mt. Pulag',3);
ins($con,'ap',1,'Heograpiya','Ano ang pinakamalaking isla sa Pilipinas?','Visayas','Mindanao','Luzon','Palawan',3);
// Level 2
ins($con,'ap',2,'Heograpiya','Ano ang "latitude"?','Distansya mula sa silangan','Distansya mula sa ekwador patimog o pahilaga','Distansya mula sa kanluran','Distansya mula sa sentro ng Mundo',2);
ins($con,'ap',2,'Heograpiya','Ano ang "longitude"?','Distansya mula sa ekwador','Distansya mula sa Prime Meridian patungo sa silangan o kanluran','Distansya mula sa hilaga','Distansya mula sa timog',2);
ins($con,'ap',2,'Heograpiya','Ano ang "ekwador"?','Linya sa hilaga','Linya sa gitna ng Mundo na nagpapaghati sa hilaga at timog','Linya sa timog','Linya sa silangan',2);
ins($con,'ap',2,'Heograpiya','Ano ang "Prime Meridian"?','Linya sa ekwador','Linya sa 0° longitude na dumadaan sa Greenwich, England','Linya sa 180° longitude','Linya sa 90° longitude',2);
ins($con,'ap',2,'Heograpiya','Ano ang "mapa"?','Larawan ng isang tao','Representasyon ng ibabaw ng Mundo o bahagi nito','Larawan ng isang hayop','Larawan ng isang gusali',2);
// Level 3
ins($con,'ap',3,'Heograpiya','Ano ang "klima"?','Panahon sa isang araw','Pangmatagalang pattern ng panahon sa isang lugar','Temperatura sa isang oras','Ulan sa isang araw',2);
ins($con,'ap',3,'Heograpiya','Ano ang "monsoon"?','Malamig na hangin','Seasonal na hangin na nagdadala ng ulan','Mainit na hangin','Malakas na bagyo',2);
ins($con,'ap',3,'Heograpiya','Ano ang "Ring of Fire"?','Lugar na may maraming apoy','Rehiyon sa paligid ng Pacific Ocean na may maraming bulkan at lindol','Lugar na mainit','Lugar na may maraming sunog',2);
ins($con,'ap',3,'Heograpiya','Ano ang "archipelago"?','Isang malaking isla','Grupo ng mga isla','Isang kontinente','Isang peninsula',2);
ins($con,'ap',3,'Heograpiya','Ano ang "watershed"?','Lugar na may maraming tubig','Lugar kung saan ang tubig ay dumadaloy patungo sa iisang ilog o katawan ng tubig','Lugar na walang tubig','Lugar na may maraming ulan',2);
// Level 4
ins($con,'ap',4,'Heograpiya','Ano ang "urbanisasyon"?','Pagpapalaki ng mga bukid','Proseso ng paglipat ng populasyon mula sa kanayunan patungo sa lungsod','Pagpapalaki ng mga kagubatan','Pagpapalaki ng mga palayan',2);
ins($con,'ap',4,'Heograpiya','Ano ang "population density"?','Kabuuang populasyon','Bilang ng tao sa bawat yunit ng lugar','Paglago ng populasyon','Pagbaba ng populasyon',2);
ins($con,'ap',4,'Heograpiya','Ano ang "migration"?','Pagbabago ng trabaho','Paglipat ng mga tao mula sa isang lugar patungo sa isa pa','Pagbabago ng kultura','Pagbabago ng relihiyon',2);
ins($con,'ap',4,'Heograpiya','Ano ang "natural resources"?','Mga produktong gawa ng tao','Mga mapagkukunan mula sa kalikasan','Mga produktong imported','Mga produktong exported',2);
ins($con,'ap',4,'Heograpiya','Ano ang "deforestation"?','Pagpapalaki ng kagubatan','Pagputol ng mga puno at pagkawala ng kagubatan','Pagpapalinis ng kagubatan','Pagpapalaki ng mga halaman',2);
// Level 5-10 (condensed)
ins($con,'ap',5,'Heograpiya','Ano ang "GIS"?','Geographic Information System','General Information System','Global Information System','Geographic Internet System',1);
ins($con,'ap',5,'Heograpiya','Ano ang "remote sensing"?','Pakikinig sa malayo','Paggamit ng satellite o aircraft upang mangolekta ng datos tungkol sa Mundo','Pakikipag-ugnayan sa malayo','Pagmamasid sa malayo',2);
ins($con,'ap',5,'Heograpiya','Ano ang "tectonic plates"?','Mga plato sa hapag-kainan','Malalaking piraso ng crust ng Mundo na gumagalaw','Mga layer ng atmospera','Mga layer ng karagatan',2);
ins($con,'ap',5,'Heograpiya','Ano ang "El Niño"?','Malamig na klima','Mainit na klima na nagdudulot ng tagtuyot sa Pilipinas','Malakas na bagyo','Malakas na lindol',2);
ins($con,'ap',5,'Heograpiya','Ano ang "La Niña"?','Mainit na klima','Malamig na klima na nagdudulot ng mas maraming ulan sa Pilipinas','Malakas na bagyo','Malakas na lindol',2);
ins($con,'ap',6,'Heograpiya','Ano ang "biodiversity"?','Iisang uri ng hayop','Pagkakaiba-iba ng mga buhay na organismo sa isang lugar','Iisang uri ng halaman','Iisang uri ng ekosistema',2);
ins($con,'ap',6,'Heograpiya','Ano ang "coral reef"?','Uri ng bundok','Ekosistema sa ilalim ng dagat na binubuo ng mga korales','Uri ng kagubatan','Uri ng palayan',2);
ins($con,'ap',6,'Heograpiya','Ano ang "mangrove"?','Uri ng bundok','Kagubatan sa baybayin na nagpoprotekta sa dalampasigan','Uri ng palayan','Uri ng kagubatan sa bundok',2);
ins($con,'ap',6,'Heograpiya','Ano ang "typhoon"?','Malamig na hangin','Malakas na bagyo na nabubuo sa Pacific Ocean','Malakas na lindol','Malakas na baha',2);
ins($con,'ap',6,'Heograpiya','Ano ang "PAGASA"?','Pangalan ng isang lungsod','Ahensyang nagmamasid sa panahon at klima ng Pilipinas','Pangalan ng isang isla','Pangalan ng isang bundok',2);
ins($con,'ap',7,'Heograpiya','Ano ang "geomorphology"?','Pag-aaral ng mga hayop','Pag-aaral ng hugis at kayarian ng ibabaw ng Mundo','Pag-aaral ng mga halaman','Pag-aaral ng mga tao',2);
ins($con,'ap',7,'Heograpiya','Ano ang "hydrological cycle"?','Siklo ng mga hayop','Siklo ng tubig sa kalikasan','Siklo ng mga halaman','Siklo ng mga tao',2);
ins($con,'ap',7,'Heograpiya','Ano ang "carbon cycle"?','Siklo ng mga hayop','Siklo ng carbon sa kalikasan','Siklo ng mga halaman','Siklo ng mga tao',2);
ins($con,'ap',7,'Heograpiya','Ano ang "soil erosion"?','Pagpapalaki ng lupa','Pagkawala ng lupa dahil sa tubig o hangin','Pagpapalinis ng lupa','Pagpapalaki ng mga halaman',2);
ins($con,'ap',7,'Heograpiya','Ano ang "urban heat island"?','Mainit na isla','Kondisyon kung saan ang mga lungsod ay mas mainit kaysa sa kanayunan','Mainit na lugar sa dagat','Mainit na lugar sa bundok',2);
ins($con,'ap',8,'Heograpiya','Ano ang "political geography"?','Pag-aaral ng kasaysayan','Pag-aaral ng relasyon ng heograpiya at pulitika','Pag-aaral ng ekonomiya','Pag-aaral ng kultura',2);
ins($con,'ap',8,'Heograpiya','Ano ang "ASEAN"?','Association of Southeast Asian Nations','Association of South Asian Nations','Association of Southeast African Nations','Association of South American Nations',1);
ins($con,'ap',8,'Heograpiya','Ano ang "exclusive economic zone" (EEZ)?','Lugar na eksklusibo para sa turismo','200-nautical mile zone kung saan ang isang bansa ay may karapatang sa mga mapagkukunan','Lugar na eksklusibo para sa militar','Lugar na eksklusibo para sa kalakalan',2);
ins($con,'ap',8,'Heograpiya','Ano ang "UNCLOS"?','United Nations Convention on the Law of the Sea','United Nations Convention on Land and Sea','United Nations Convention on the Law of Space','United Nations Convention on Land and Space',1);
ins($con,'ap',8,'Heograpiya','Ano ang "West Philippine Sea"?','Dagat sa silangan ng Pilipinas','Bahagi ng South China Sea na inaangkin ng Pilipinas','Dagat sa hilaga ng Pilipinas','Dagat sa timog ng Pilipinas',2);
ins($con,'ap',9,'Heograpiya','Ano ang "critical geography"?','Tradisyonal na heograpiya','Pag-aaral ng heograpiya na may kritikal na pananaw sa kapangyarihan at lipunan','Pag-aaral ng kasaysayan','Pag-aaral ng ekonomiya',2);
ins($con,'ap',9,'Heograpiya','Ano ang "feminist geography"?','Heograpiya para sa kababaihan lamang','Pag-aaral ng heograpiya sa pamamagitan ng lente ng kasarian','Heograpiya para sa kalalakihan lamang','Heograpiya para sa mga bata lamang',2);
ins($con,'ap',9,'Heograpiya','Ano ang "postcolonial geography"?','Heograpiya ng mga kolonya','Pag-aaral ng heograpiya na may kritikal na pananaw sa kolonyalismo','Heograpiya ng mga mananakop','Heograpiya ng mga bansa',2);
ins($con,'ap',9,'Heograpiya','Ano ang "spatial justice"?','Katarungan sa espasyo','Pantay na pamamahagi ng mga mapagkukunan at oportunidad sa espasyo','Katarungan sa lugar','Katarungan sa lupa',2);
ins($con,'ap',9,'Heograpiya','Ano ang "geovisualization"?','Pagpipinta ng mapa','Paggamit ng visual na representasyon upang pag-aralan ang datos ng heograpiya','Paglikha ng mapa','Pagbabago ng mapa',2);
ins($con,'ap',10,'Heograpiya','Ano ang "Anthropocene geography"?','Heograpiya ng mga hayop','Pag-aaral ng epekto ng tao sa heograpiya ng Mundo','Heograpiya ng mga halaman','Heograpiya ng mga bundok',2);
ins($con,'ap',10,'Heograpiya','Ano ang "big data" sa heograpiya?','Malaking mapa','Paggamit ng malalaking datos upang pag-aralan ang mga pattern ng heograpiya','Malaking atlas','Malaking globo',2);
ins($con,'ap',10,'Heograpiya','Ano ang "smart city"?','Lungsod na may maraming tao','Lungsod na gumagamit ng teknolohiya upang mapabuti ang serbisyo at kalidad ng buhay','Lungsod na may maraming gusali','Lungsod na may maraming kalsada',2);
ins($con,'ap',10,'Heograpiya','Ano ang "resilience" sa heograpiya?','Kahinaan ng isang lugar','Kakayahan ng isang lugar na makabawi mula sa mga sakuna','Lakas ng isang lugar','Yaman ng isang lugar',2);
ins($con,'ap',10,'Heograpiya','Ano ang "geopolitical risk"?','Panganib sa kalikasan','Panganib na dulot ng pulitikal na sitwasyon sa isang lugar','Panganib sa ekonomiya','Panganib sa kultura',2);

// ============================================================
// AP — Pamahalaan at Lipunan
// ============================================================
// Level 1
ins($con,'ap',1,'Pamahalaan at Lipunan','Ano ang "pamahalaan"?','Isang negosyo','Organisasyong namamahala sa isang bansa o lugar','Isang paaralan','Isang simbahan',2);
ins($con,'ap',1,'Pamahalaan at Lipunan','Ano ang "demokrasya"?','Pamahalaang pinamumunuan ng isang tao','Pamahalaang pinamumunuan ng mamamayan','Pamahalaang pinamumunuan ng militar','Pamahalaang pinamumunuan ng relihiyon',2);
ins($con,'ap',1,'Pamahalaan at Lipunan','Ano ang "konstitusyon"?','Isang batas lamang','Pangunahing batas ng isang bansa','Isang utos ng pangulo','Isang desisyon ng korte',2);
ins($con,'ap',1,'Pamahalaan at Lipunan','Ano ang "pangulo"?','Pinuno ng kongreso','Pinuno ng ehekutibong sangay ng pamahalaan','Pinuno ng hudikatura','Pinuno ng militar',2);
ins($con,'ap',1,'Pamahalaan at Lipunan','Ano ang "batas"?','Isang utos ng pangulo','Panuntunan na ipinapatupad ng pamahalaan','Isang desisyon ng korte','Isang kasunduan',2);
// Level 2
ins($con,'ap',2,'Pamahalaan at Lipunan','Ano ang tatlong sangay ng pamahalaan ng Pilipinas?','Pangulo, Senado, Korte','Lehislatibo, Ehekutibo, Hudikatura','Kongreso, Pangulo, Militar','Senado, Kamara, Korte',2);
ins($con,'ap',2,'Pamahalaan at Lipunan','Ano ang "Kongreso"?','Ehekutibong sangay','Lehislatibong sangay na binubuo ng Senado at Kamara ng mga Kinatawan','Hudisyal na sangay','Militar',2);
ins($con,'ap',2,'Pamahalaan at Lipunan','Ano ang "Korte Suprema"?','Pinakamababang korte','Pinakamataas na korte sa Pilipinas','Korte ng mga negosyo','Korte ng mga kriminal',2);
ins($con,'ap',2,'Pamahalaan at Lipunan','Ano ang "lokal na pamahalaan"?','Pambansang pamahalaan','Pamahalaan ng lalawigan, lungsod, bayan, at barangay','Pamahalaan ng rehiyon','Pamahalaan ng mga organisasyon',2);
ins($con,'ap',2,'Pamahalaan at Lipunan','Ano ang "barangay"?','Pinakamataas na yunit ng pamahalaan','Pinakamaliit na yunit ng lokal na pamahalaan','Isang lungsod','Isang lalawigan',2);
// Level 3
ins($con,'ap',3,'Pamahalaan at Lipunan','Ano ang "checks and balances"?','Pagbabayad ng buwis','Sistema ng pagkontrol ng bawat sangay ng pamahalaan sa isa\'t isa','Pagbabayad ng utang','Pagkontrol ng militar',2);
ins($con,'ap',3,'Pamahalaan at Lipunan','Ano ang "separation of powers"?','Paghihiwalay ng mga tao','Paghihiwalay ng kapangyarihan sa tatlong sangay ng pamahalaan','Paghihiwalay ng mga batas','Paghihiwalay ng mga bansa',2);
ins($con,'ap',3,'Pamahalaan at Lipunan','Ano ang "suffrage"?','Karapatang magtrabaho','Karapatang bumoto','Karapatang mag-aral','Karapatang magsalita',2);
ins($con,'ap',3,'Pamahalaan at Lipunan','Ano ang "civil society"?','Pamahalaan','Mga organisasyong hindi bahagi ng pamahalaan o negosyo','Militar','Negosyo',2);
ins($con,'ap',3,'Pamahalaan at Lipunan','Ano ang "rule of law"?','Pamumuno ng isang tao','Prinsipyo na ang lahat ay saklaw ng batas','Pamumuno ng militar','Pamumuno ng relihiyon',2);
// Level 4
ins($con,'ap',4,'Pamahalaan at Lipunan','Ano ang "federalism"?','Unitaryong pamahalaan','Sistema ng pamahalaan kung saan ang kapangyarihan ay nahahati sa pambansa at lokal na pamahalaan','Diktadura','Monarkiya',2);
ins($con,'ap',4,'Pamahalaan at Lipunan','Ano ang "unitary system"?','Pederal na sistema','Sistema kung saan ang kapangyarihan ay nakasentro sa pambansang pamahalaan','Diktadura','Monarkiya',2);
ins($con,'ap',4,'Pamahalaan at Lipunan','Ano ang "political party"?','Isang negosyo','Organisasyong nagtataguyod ng mga kandidato at patakaran sa pulitika','Isang relihiyosong grupo','Isang pangkat ng mga kaibigan',2);
ins($con,'ap',4,'Pamahalaan at Lipunan','Ano ang "election"?','Pagpili ng mga produkto','Proseso ng pagpili ng mga opisyal ng pamahalaan sa pamamagitan ng pagboto','Pagpili ng mga trabaho','Pagpili ng mga paaralan',2);
ins($con,'ap',4,'Pamahalaan at Lipunan','Ano ang "COMELEC"?','Commission on Elections','Committee on Elections','Commission on Economic Laws','Committee on Economic Laws',1);
// Level 5-10 (condensed)
ins($con,'ap',5,'Pamahalaan at Lipunan','Ano ang "social contract"?','Kontrata sa trabaho','Teorya na ang pamahalaan ay batay sa kasunduan ng mga mamamayan','Kontrata sa negosyo','Kontrata sa paaralan',2);
ins($con,'ap',5,'Pamahalaan at Lipunan','Sino si John Locke?','Pilipinong pilosopo','Ingles na pilosopong nagtataguyod ng natural rights at limited government','Amerikanong pilosopo','Pranses na pilosopo',2);
ins($con,'ap',5,'Pamahalaan at Lipunan','Ano ang "natural rights" (Locke)?','Karapatang ibinibigay ng pamahalaan','Karapatang likas na pag-aari ng bawat tao: buhay, kalayaan, at ari-arian','Karapatang ibinibigay ng relihiyon','Karapatang ibinibigay ng lipunan',2);
ins($con,'ap',5,'Pamahalaan at Lipunan','Ano ang "general will" (Rousseau)?','Kagustuhan ng isang tao','Kagustuhan ng lahat para sa kabutihan ng lahat','Kagustuhan ng pamahalaan','Kagustuhan ng mga mayayaman',2);
ins($con,'ap',5,'Pamahalaan at Lipunan','Ano ang "Leviathan" (Hobbes)?','Isang hayop','Makapangyarihang pamahalaan na kailangan upang mapanatili ang kaayusan','Isang batas','Isang kasunduan',2);
ins($con,'ap',6,'Pamahalaan at Lipunan','Ano ang "authoritarianism"?','Demokrasya','Sistema ng pamahalaan na may malakas na kontrol at limitadong kalayaan','Sosyalismo','Komunismo',2);
ins($con,'ap',6,'Pamahalaan at Lipunan','Ano ang "totalitarianism"?','Demokrasya','Sistema ng pamahalaan na may ganap na kontrol sa lahat ng aspeto ng buhay','Sosyalismo','Federalismo',2);
ins($con,'ap',6,'Pamahalaan at Lipunan','Ano ang "civil liberties"?','Karapatang pang-ekonomiya','Mga karapatang nagpoprotekta sa indibidwal mula sa pamahalaan','Karapatang panlipunan','Karapatang pang-edukasyon',2);
ins($con,'ap',6,'Pamahalaan at Lipunan','Ano ang "judicial review"?','Pagsusuri ng mga negosyo','Kapangyarihan ng korte na suriin ang konstitusyonalidad ng mga batas','Pagsusuri ng mga batas','Pagsusuri ng mga opisyal',2);
ins($con,'ap',6,'Pamahalaan at Lipunan','Ano ang "impeachment"?','Paghalal ng opisyal','Proseso ng pag-aalis ng opisyal sa pamamagitan ng lehislatibo','Pagpapalit ng opisyal','Pagpili ng opisyal',2);
ins($con,'ap',7,'Pamahalaan at Lipunan','Ano ang "pluralism"?','Iisang grupo ang may kapangyarihan','Maraming grupo ang may kapangyarihan sa lipunan','Iisang partido ang may kapangyarihan','Iisang relihiyon ang may kapangyarihan',2);
ins($con,'ap',7,'Pamahalaan at Lipunan','Ano ang "elitism"?','Lahat ay may kapangyarihan','Iisang maliit na grupo ang may kapangyarihan','Maraming grupo ang may kapangyarihan','Walang may kapangyarihan',2);
ins($con,'ap',7,'Pamahalaan at Lipunan','Ano ang "bureaucracy"?','Simpleng pamahalaan','Sistemang administratibo ng pamahalaan na may mga opisyal at proseso','Militar','Kongreso',2);
ins($con,'ap',7,'Pamahalaan at Lipunan','Ano ang "lobbying"?','Pagboto','Pagtatangkang impluwensyahan ang mga opisyal ng pamahalaan','Pagprotesta','Pagbabago ng batas',2);
ins($con,'ap',7,'Pamahalaan at Lipunan','Ano ang "propaganda"?','Totoong impormasyon','Impormasyon na ginagamit upang impluwensyahan ang opinyon ng publiko','Neutral na impormasyon','Siyentipikong impormasyon',2);
ins($con,'ap',8,'Pamahalaan at Lipunan','Ano ang "deliberative democracy"?','Simpleng demokrasya','Demokrasyang nagbibigay-diin sa talakayan at debate','Diktadura','Monarkiya',2);
ins($con,'ap',8,'Pamahalaan at Lipunan','Ano ang "participatory democracy"?','Representatibong demokrasya','Demokrasyang nagbibigay-diin sa direktang pakikilahok ng mamamayan','Diktadura','Monarkiya',2);
ins($con,'ap',8,'Pamahalaan at Lipunan','Ano ang "governance"?','Pamahalaan lamang','Proseso ng pamamahala na kinabibilangan ng pamahalaan, negosyo, at civil society','Negosyo lamang','Civil society lamang',2);
ins($con,'ap',8,'Pamahalaan at Lipunan','Ano ang "transparency"?','Pagiging lihim','Pagiging bukas at accessible ang impormasyon ng pamahalaan','Pagiging kumplikado','Pagiging mahirap',2);
ins($con,'ap',8,'Pamahalaan at Lipunan','Ano ang "accountability"?','Walang responsibilidad','Responsibilidad ng mga opisyal sa kanilang mga aksyon','Kalayaan ng mga opisyal','Kapangyarihan ng mga opisyal',2);
ins($con,'ap',9,'Pamahalaan at Lipunan','Ano ang "social capital"?','Pera ng lipunan','Mga network, tiwala, at pamantayan na nagpapadali ng kooperasyon sa lipunan','Yaman ng lipunan','Kaalaman ng lipunan',2);
ins($con,'ap',9,'Pamahalaan at Lipunan','Ano ang "civil disobedience"?','Pagsunod sa batas','Sadyang paglabag sa batas upang protesta laban sa kawalang-katarungan','Pagbabago ng batas','Pagpapatupad ng batas',2);
ins($con,'ap',9,'Pamahalaan at Lipunan','Ano ang "political socialization"?','Pagbabago ng pulitika','Proseso ng pagkatuto ng mga pagpapahalaga at paniniwala sa pulitika','Pagbabago ng lipunan','Pagbabago ng kultura',2);
ins($con,'ap',9,'Pamahalaan at Lipunan','Ano ang "hegemony" (Gramsci)?','Pulitikal na kapangyarihan','Dominasyon ng isang grupo sa pamamagitan ng kultura at ideolohiya','Ekonomikong kapangyarihan','Militar na kapangyarihan',2);
ins($con,'ap',9,'Pamahalaan at Lipunan','Ano ang "state apparatus" (Althusser)?','Mga kasangkapan ng estado','Mga institusyong ginagamit ng estado upang mapanatili ang kapangyarihan','Mga kasangkapan ng militar','Mga kasangkapan ng negosyo',2);
ins($con,'ap',10,'Pamahalaan at Lipunan','Ano ang "biopower" (Foucault)?','Kapangyarihan ng mga hayop','Kapangyarihan ng estado sa katawan at buhay ng mga mamamayan','Kapangyarihan ng kalikasan','Kapangyarihan ng teknolohiya',2);
ins($con,'ap',10,'Pamahalaan at Lipunan','Ano ang "governmentality" (Foucault)?','Simpleng pamamahala','Mga teknik at rasyonalidad ng pamamahala ng populasyon','Militar na pamamahala','Ekonomikong pamamahala',2);
ins($con,'ap',10,'Pamahalaan at Lipunan','Ano ang "agonistic democracy" (Mouffe)?','Mapayapang demokrasya','Demokrasyang kinikilala ang hindi malulutas na pulitikal na hidwaan','Diktadura','Monarkiya',2);
ins($con,'ap',10,'Pamahalaan at Lipunan','Ano ang "post-democracy" (Crouch)?','Mas mataas na demokrasya','Kondisyon kung saan ang mga anyo ng demokrasya ay nananatili ngunit ang substansya ay nababawasan','Diktadura','Monarkiya',2);
ins($con,'ap',10,'Pamahalaan at Lipunan','Ano ang "liquid modernity" (Bauman)?','Solidong modernidad','Kondisyon ng modernidad na nailalarawan ng pagbabago at kawalan ng katiyakan','Tradisyonal na lipunan','Modernong lipunan',2);

// ============================================================
// SCIENCE — Biology
// ============================================================
// Level 1
ins($con,'science',1,'Biology','What is the basic unit of life?','Atom','Molecule','Cell','Tissue',3);
ins($con,'science',1,'Biology','What do plants need to make food?','Water only','Sunlight, water, and carbon dioxide','Soil only','Oxygen only',2);
ins($con,'science',1,'Biology','What is photosynthesis?','Process of breathing','Process by which plants make food using sunlight','Process of digestion','Process of reproduction',2);
ins($con,'science',1,'Biology','What is the function of the heart?','To digest food','To pump blood through the body','To filter air','To produce hormones',2);
ins($con,'science',1,'Biology','What do animals need to survive?','Sunlight only','Food, water, air, and shelter','Soil only','Carbon dioxide only',2);
// Level 2
ins($con,'science',2,'Biology','What is the function of the cell membrane?','To produce energy','To control what enters and exits the cell','To store genetic information','To produce proteins',2);
ins($con,'science',2,'Biology','What organelle is called the "powerhouse of the cell"?','Nucleus','Ribosome','Mitochondria','Chloroplast',3);
ins($con,'science',2,'Biology','What is the function of the nucleus?','To produce energy','To control cell activities and store DNA','To produce proteins','To digest food',2);
ins($con,'science',2,'Biology','What is the difference between plant and animal cells?','No difference','Plant cells have cell wall and chloroplasts; animal cells do not','Animal cells have cell wall; plant cells do not','Plant cells have mitochondria; animal cells do not',2);
ins($con,'science',2,'Biology','What is respiration?','Process of breathing only','Process of converting glucose to energy','Process of photosynthesis','Process of digestion',2);
// Level 3
ins($con,'science',3,'Biology','What is DNA?','A type of protein','Molecule that carries genetic information','A type of carbohydrate','A type of lipid',2);
ins($con,'science',3,'Biology','What is mitosis?','Cell death','Cell division producing two identical cells','Cell division producing four cells','Cell growth',2);
ins($con,'science',3,'Biology','What is meiosis?','Cell death','Cell division producing four genetically different cells','Cell division producing two identical cells','Cell growth',2);
ins($con,'science',3,'Biology','What is an ecosystem?','A single organism','Community of organisms and their environment','A type of cell','A type of tissue',2);
ins($con,'science',3,'Biology','What is a food chain?','A chain of restaurants','Sequence showing how energy passes from one organism to another','A type of ecosystem','A type of cell',2);
// Level 4
ins($con,'science',4,'Biology','What is natural selection?','Random change in species','Process by which organisms with favorable traits survive and reproduce','Artificial breeding','Genetic engineering',2);
ins($con,'science',4,'Biology','What is a gene?','A type of cell','A segment of DNA that codes for a specific trait','A type of protein','A type of chromosome',2);
ins($con,'science',4,'Biology','What is a chromosome?','A type of cell','Structure in the nucleus containing DNA','A type of protein','A type of organelle',2);
ins($con,'science',4,'Biology','What is homeostasis?','Cell division','Maintenance of stable internal conditions','Cell growth','Cell death',2);
ins($con,'science',4,'Biology','What is the function of the nervous system?','To digest food','To coordinate body activities and respond to stimuli','To pump blood','To filter waste',2);
// Level 5
ins($con,'science',5,'Biology','What is osmosis?','Movement of solutes across a membrane','Movement of water across a semi-permeable membrane from low to high solute concentration','Movement of gases','Movement of proteins',2);
ins($con,'science',5,'Biology','What is diffusion?','Movement of water only','Movement of particles from high to low concentration','Movement of proteins only','Movement of cells',2);
ins($con,'science',5,'Biology','What is the role of enzymes?','To store energy','To speed up chemical reactions in the body','To carry oxygen','To produce hormones',2);
ins($con,'science',5,'Biology','What is the immune system?','System for digestion','System that defends the body against pathogens','System for reproduction','System for movement',2);
ins($con,'science',5,'Biology','What is a pathogen?','A beneficial organism','A disease-causing microorganism','A type of cell','A type of tissue',2);
// Level 6
ins($con,'science',6,'Biology','What is the central dogma of molecular biology?','DNA → RNA → Protein','Protein → RNA → DNA','RNA → DNA → Protein','DNA → Protein → RNA',1);
ins($con,'science',6,'Biology','What is transcription?','DNA replication','Process of making RNA from DNA','Process of making protein from RNA','Process of making DNA from RNA',2);
ins($con,'science',6,'Biology','What is translation?','Language conversion','Process of making protein from mRNA','Process of making RNA from DNA','Process of DNA replication',2);
ins($con,'science',6,'Biology','What is a mutation?','Normal cell division','Change in the DNA sequence','Normal cell growth','Normal protein synthesis',2);
ins($con,'science',6,'Biology','What is genetic engineering?','Natural selection','Direct manipulation of an organism\'s genes','Artificial selection','Natural mutation',2);
// Level 7
ins($con,'science',7,'Biology','What is CRISPR?','A type of virus','Gene editing technology using bacterial immune system','A type of protein','A type of cell',2);
ins($con,'science',7,'Biology','What is epigenetics?','Study of genes only','Study of heritable changes in gene expression without DNA sequence changes','Study of mutations','Study of chromosomes',2);
ins($con,'science',7,'Biology','What is the Hardy-Weinberg principle?','About natural selection','Allele frequencies remain constant in absence of evolutionary forces','About genetic drift','About mutation',2);
ins($con,'science',7,'Biology','What is symbiosis?','Competition between species','Close interaction between two different species','Predation','Parasitism only',2);
ins($con,'science',7,'Biology','What is coevolution?','Evolution of one species','Reciprocal evolutionary change between interacting species','Random evolution','Directed evolution',2);
// Level 8
ins($con,'science',8,'Biology','What is the endosymbiotic theory?','About cell division','Theory that mitochondria and chloroplasts evolved from ancient bacteria','About DNA replication','About protein synthesis',2);
ins($con,'science',8,'Biology','What is apoptosis?','Cell growth','Programmed cell death','Cell division','Cell mutation',2);
ins($con,'science',8,'Biology','What is the lac operon?','A type of gene','Regulatory system in bacteria for lactose metabolism','A type of protein','A type of chromosome',2);
ins($con,'science',8,'Biology','What is horizontal gene transfer?','Vertical inheritance','Transfer of genes between organisms other than parent to offspring','Mutation','Recombination',2);
ins($con,'science',8,'Biology','What is the metagenome?','Genome of one organism','Collective genome of all organisms in an environment','Genome of a virus','Genome of a bacterium',2);
// Level 9
ins($con,'science',9,'Biology','What is systems biology?','Study of individual genes','Holistic study of biological systems and their interactions','Study of individual cells','Study of individual proteins',2);
ins($con,'science',9,'Biology','What is proteomics?','Study of genes','Large-scale study of proteins in a cell or organism','Study of RNA','Study of DNA',2);
ins($con,'science',9,'Biology','What is the microbiome?','A single bacterium','Community of microorganisms living in or on an organism','A type of virus','A type of fungus',2);
ins($con,'science',9,'Biology','What is synthetic biology?','Natural biology','Design and construction of new biological parts and systems','Study of evolution','Study of ecology',2);
ins($con,'science',9,'Biology','What is optogenetics?','Study of light','Technique using light to control genetically modified neurons','Study of genetics','Study of optics',2);
// Level 10
ins($con,'science',10,'Biology','What is the RNA world hypothesis?','DNA came first','RNA was the first self-replicating molecule before DNA and proteins','Proteins came first','Cells came first',2);
ins($con,'science',10,'Biology','What is the Cambrian explosion?','A volcanic event','Rapid diversification of animal life ~541 million years ago','A mass extinction','A climate event',2);
ins($con,'science',10,'Biology','What is evo-devo?','Study of evolution only','Study of how developmental processes evolve','Study of development only','Study of genetics only',2);
ins($con,'science',10,'Biology','What is the neutral theory of molecular evolution?','All mutations are harmful','Most molecular evolution is due to neutral mutations','All mutations are beneficial','All mutations are selected',2);
ins($con,'science',10,'Biology','What is the extended evolutionary synthesis?','Traditional Darwinism','Expansion of evolutionary theory including epigenetics, niche construction, etc.','Creationism','Intelligent design',2);

// ============================================================
// SCIENCE — Chemistry
// ============================================================
// Level 1
ins($con,'science',1,'Chemistry','What is an atom?','A type of molecule','The smallest unit of an element','A type of compound','A type of mixture',2);
ins($con,'science',1,'Chemistry','What is water made of?','Hydrogen and nitrogen','Hydrogen and oxygen','Oxygen and carbon','Hydrogen and carbon',2);
ins($con,'science',1,'Chemistry','What is the symbol for gold?','Go','Gd','Au','Ag',3);
ins($con,'science',1,'Chemistry','What is the symbol for oxygen?','O','Ox','On','Om',1);
ins($con,'science',1,'Chemistry','What is a "mixture"?','A pure substance','A combination of two or more substances not chemically combined','A single element','A single compound',2);
// Level 2
ins($con,'science',2,'Chemistry','What is the periodic table?','A table of food','A table organizing elements by atomic number and properties','A table of compounds','A table of mixtures',2);
ins($con,'science',2,'Chemistry','What is an element?','A compound','A pure substance made of only one type of atom','A mixture','A molecule',2);
ins($con,'science',2,'Chemistry','What is a compound?','A mixture','A substance made of two or more elements chemically combined','A single element','A single atom',2);
ins($con,'science',2,'Chemistry','What is the atomic number?','Number of neutrons','Number of protons in the nucleus','Number of electrons','Number of neutrons and protons',2);
ins($con,'science',2,'Chemistry','What is a chemical reaction?','Physical change','Process where substances are transformed into new substances','Change in state','Change in shape',2);
// Level 3
ins($con,'science',3,'Chemistry','What is the law of conservation of mass?','Mass is created in reactions','Mass is neither created nor destroyed in a chemical reaction','Mass is destroyed in reactions','Mass changes in reactions',2);
ins($con,'science',3,'Chemistry','What is an acid?','A substance with pH > 7','A substance that donates protons (H⁺)','A substance with pH = 7','A substance that accepts protons',2);
ins($con,'science',3,'Chemistry','What is a base?','A substance with pH < 7','A substance that accepts protons (H⁺)','A substance with pH = 7','A substance that donates protons',2);
ins($con,'science',3,'Chemistry','What is pH?','Measure of temperature','Measure of acidity or alkalinity','Measure of density','Measure of mass',2);
ins($con,'science',3,'Chemistry','What is the pH of pure water?','5','6','7','8',3);
// Level 4
ins($con,'science',4,'Chemistry','What is a covalent bond?','Bond formed by transfer of electrons','Bond formed by sharing of electrons','Bond formed by attraction of ions','Bond formed by metallic electrons',2);
ins($con,'science',4,'Chemistry','What is an ionic bond?','Bond formed by sharing electrons','Bond formed by transfer of electrons between atoms','Bond formed by metallic electrons','Bond formed by hydrogen',2);
ins($con,'science',4,'Chemistry','What is oxidation?','Gain of electrons','Loss of electrons','Gain of protons','Loss of protons',2);
ins($con,'science',4,'Chemistry','What is reduction?','Loss of electrons','Gain of electrons','Loss of protons','Gain of protons',2);
ins($con,'science',4,'Chemistry','What is a catalyst?','A substance consumed in a reaction','A substance that speeds up a reaction without being consumed','A substance that slows a reaction','A substance that stops a reaction',2);
// Level 5
ins($con,'science',5,'Chemistry','What is the mole?','A small animal','Unit of amount of substance (6.022 × 10²³ particles)','A unit of mass','A unit of volume',2);
ins($con,'science',5,'Chemistry','What is Avogadro\'s number?','6.022 × 10²²','6.022 × 10²³','6.022 × 10²⁴','6.022 × 10²¹',2);
ins($con,'science',5,'Chemistry','What is molarity?','Moles per kilogram','Moles of solute per liter of solution','Grams per liter','Grams per mole',2);
ins($con,'science',5,'Chemistry','What is an exothermic reaction?','Reaction that absorbs heat','Reaction that releases heat','Reaction that requires light','Reaction that requires electricity',2);
ins($con,'science',5,'Chemistry','What is an endothermic reaction?','Reaction that releases heat','Reaction that absorbs heat','Reaction that requires electricity','Reaction that releases light',2);
// Level 6
ins($con,'science',6,'Chemistry','What is Le Chatelier\'s principle?','About reaction rates','System at equilibrium shifts to counteract changes','About catalysts','About activation energy',2);
ins($con,'science',6,'Chemistry','What is the ideal gas law?','PV = nRT','PV = RT','P = nRT','PV = nR',1);
ins($con,'science',6,'Chemistry','What is electronegativity?','Ability to lose electrons','Ability of an atom to attract electrons in a bond','Ability to gain protons','Ability to lose protons',2);
ins($con,'science',6,'Chemistry','What is a buffer solution?','A solution that changes pH easily','A solution that resists changes in pH','A solution with pH = 7','A solution with pH = 0',2);
ins($con,'science',6,'Chemistry','What is titration?','A type of reaction','Technique to determine concentration using a known solution','A type of separation','A type of distillation',2);
// Level 7
ins($con,'science',7,'Chemistry','What is hybridization in chemistry?','Mixing of species','Mixing of atomic orbitals to form new hybrid orbitals','Mixing of molecules','Mixing of compounds',2);
ins($con,'science',7,'Chemistry','What is VSEPR theory?','About reaction rates','Theory predicting molecular geometry based on electron pair repulsion','About catalysts','About activation energy',2);
ins($con,'science',7,'Chemistry','What is entropy?','Order in a system','Measure of disorder or randomness in a system','Energy in a system','Temperature of a system',2);
ins($con,'science',7,'Chemistry','What is Gibbs free energy?','Total energy','Energy available to do work at constant temperature and pressure','Kinetic energy','Potential energy',2);
ins($con,'science',7,'Chemistry','What is a redox reaction?','Acid-base reaction','Reaction involving transfer of electrons','Precipitation reaction','Combustion reaction',2);
// Level 8
ins($con,'science',8,'Chemistry','What is the Heisenberg uncertainty principle?','About reaction rates','Cannot simultaneously know exact position and momentum of a particle','About catalysts','About activation energy',2);
ins($con,'science',8,'Chemistry','What is quantum mechanics in chemistry?','Classical physics','Study of behavior of matter at atomic and subatomic levels','Study of large molecules','Study of reactions',2);
ins($con,'science',8,'Chemistry','What is NMR spectroscopy?','Nuclear Magnetic Resonance - used to determine molecular structure','Nuclear Mass Resonance','Normal Magnetic Resonance','Nuclear Molecular Resonance',1);
ins($con,'science',8,'Chemistry','What is chromatography?','A type of reaction','Technique to separate mixtures based on differential movement','A type of distillation','A type of filtration',2);
ins($con,'science',8,'Chemistry','What is the Born-Haber cycle?','About reaction rates','Thermodynamic cycle for calculating lattice energy of ionic compounds','About catalysts','About activation energy',2);
// Level 9
ins($con,'science',9,'Chemistry','What is density functional theory (DFT)?','About reaction rates','Computational method for studying electronic structure','About catalysts','About activation energy',2);
ins($con,'science',9,'Chemistry','What is green chemistry?','Chemistry of plants','Design of chemical products and processes that reduce hazardous substances','Chemistry of the environment','Chemistry of food',2);
ins($con,'science',9,'Chemistry','What is supramolecular chemistry?','Study of single molecules','Study of non-covalent interactions between molecules','Study of atoms','Study of ions',2);
ins($con,'science',9,'Chemistry','What is nanotechnology in chemistry?','Study of large molecules','Manipulation of matter at the nanoscale (1-100 nm)','Study of reactions','Study of mixtures',2);
ins($con,'science',9,'Chemistry','What is the Marcus theory?','About acid-base reactions','Theory of electron transfer rates in chemical reactions','About catalysts','About activation energy',2);
// Level 10
ins($con,'science',10,'Chemistry','What is the Woodward-Hoffmann rules?','About reaction rates','Rules predicting stereochemistry of pericyclic reactions','About catalysts','About activation energy',2);
ins($con,'science',10,'Chemistry','What is retrosynthetic analysis?','Forward synthesis planning','Backward analysis to plan synthesis of complex molecules','Study of reactions','Study of mechanisms',2);
ins($con,'science',10,'Chemistry','What is the Diels-Alder reaction?','Acid-base reaction','[4+2] cycloaddition reaction forming six-membered rings','Redox reaction','Substitution reaction',2);
ins($con,'science',10,'Chemistry','What is organocatalysis?','Metal-catalyzed reactions','Catalysis using small organic molecules','Enzyme catalysis','Acid-base catalysis',2);
ins($con,'science',10,'Chemistry','What is the concept of aromaticity?','About aliphatic compounds','Special stability of cyclic, planar, conjugated systems following Hückel\'s rule','About saturated compounds','About ionic compounds',2);

// ============================================================
// SCIENCE — Physics
// ============================================================
// Level 1
ins($con,'science',1,'Physics','What is gravity?','A type of light','Force that attracts objects toward each other','A type of sound','A type of heat',2);
ins($con,'science',1,'Physics','What is the unit of force?','Meter','Kilogram','Newton','Joule',3);
ins($con,'science',1,'Physics','What is speed?','Distance × time','Distance ÷ time','Time ÷ distance','Distance + time',2);
ins($con,'science',1,'Physics','What is energy?','A type of force','Ability to do work','A type of matter','A type of wave',2);
ins($con,'science',1,'Physics','What is the unit of energy?','Newton','Watt','Joule','Meter',3);
// Level 2
ins($con,'science',2,'Physics','What is Newton\'s first law?','F = ma','An object at rest stays at rest unless acted upon by a force','For every action there is an equal and opposite reaction','Energy is conserved',2);
ins($con,'science',2,'Physics','What is Newton\'s second law?','An object at rest stays at rest','F = ma','For every action there is an equal and opposite reaction','Energy is conserved',2);
ins($con,'science',2,'Physics','What is Newton\'s third law?','F = ma','An object at rest stays at rest','For every action there is an equal and opposite reaction','Energy is conserved',3);
ins($con,'science',2,'Physics','What is the unit of power?','Joule','Newton','Watt','Meter',3);
ins($con,'science',2,'Physics','What is the formula for kinetic energy?','KE = mgh','KE = ½mv²','KE = mv','KE = ½mgh',2);
// Level 3
ins($con,'science',3,'Physics','What is the formula for potential energy?','PE = ½mv²','PE = mgh','PE = mv','PE = ½mgh',2);
ins($con,'science',3,'Physics','What is the law of conservation of energy?','Energy is created','Energy cannot be created or destroyed, only transformed','Energy is destroyed','Energy decreases over time',2);
ins($con,'science',3,'Physics','What is the formula for work?','W = m × a','W = F × d','W = F × t','W = m × v',2);
ins($con,'science',3,'Physics','What is the speed of light?','3 × 10⁶ m/s','3 × 10⁷ m/s','3 × 10⁸ m/s','3 × 10⁹ m/s',3);
ins($con,'science',3,'Physics','What is the formula for pressure?','P = F × A','P = F ÷ A','P = A ÷ F','P = F + A',2);
// Level 4
ins($con,'science',4,'Physics','What is Ohm\'s law?','V = IR','V = I/R','V = I + R','V = I - R',1);
ins($con,'science',4,'Physics','What is the unit of electric current?','Volt','Ohm','Ampere','Watt',3);
ins($con,'science',4,'Physics','What is the unit of electric resistance?','Volt','Ohm','Ampere','Watt',2);
ins($con,'science',4,'Physics','What is the formula for electric power?','P = V/I','P = VI','P = V + I','P = V - I',2);
ins($con,'science',4,'Physics','What is the law of conservation of momentum?','Momentum is created','Total momentum of a closed system remains constant','Momentum is destroyed','Momentum decreases over time',2);
// Level 5
ins($con,'science',5,'Physics','What is the Doppler effect?','Change in color of light','Change in frequency of a wave due to relative motion','Change in amplitude of a wave','Change in wavelength only',2);
ins($con,'science',5,'Physics','What is the formula for wave speed?','v = f/λ','v = fλ','v = λ/f','v = f + λ',2);
ins($con,'science',5,'Physics','What is the electromagnetic spectrum?','Visible light only','Range of all electromagnetic radiation from radio waves to gamma rays','Sound waves only','Mechanical waves only',2);
ins($con,'science',5,'Physics','What is the formula for gravitational force?','F = mg','F = Gm₁m₂/r²','F = ma','F = mv²/r',2);
ins($con,'science',5,'Physics','What is the principle of superposition?','Waves cancel each other','When two waves meet, the resultant displacement is the sum of individual displacements','Waves multiply each other','Waves divide each other',2);
// Level 6
ins($con,'science',6,'Physics','What is the Bernoulli principle?','About gravity','As fluid speed increases, pressure decreases','About electricity','About magnetism',2);
ins($con,'science',6,'Physics','What is Archimedes\' principle?','About gravity','Buoyant force equals weight of fluid displaced','About electricity','About magnetism',2);
ins($con,'science',6,'Physics','What is the first law of thermodynamics?','Energy is destroyed','Energy is conserved: ΔU = Q - W','Entropy always increases','Heat flows from cold to hot',2);
ins($con,'science',6,'Physics','What is the second law of thermodynamics?','Energy is conserved','Entropy of an isolated system always increases','Heat flows from cold to hot','Energy is created',2);
ins($con,'science',6,'Physics','What is the photoelectric effect?','Light bends around objects','Emission of electrons when light hits a metal surface','Light reflects off surfaces','Light refracts through glass',2);
// Level 7
ins($con,'science',7,'Physics','What is special relativity?','About gravity','Theory that the laws of physics are the same for all non-accelerating observers','About quantum mechanics','About thermodynamics',2);
ins($con,'science',7,'Physics','What is E = mc²?','Energy equals mass times speed','Energy equals mass times speed of light squared','Energy equals mass times constant','Energy equals momentum times speed',2);
ins($con,'science',7,'Physics','What is the Heisenberg uncertainty principle?','About reaction rates','Cannot simultaneously know exact position and momentum of a particle','About catalysts','About activation energy',2);
ins($con,'science',7,'Physics','What is wave-particle duality?','Waves are particles','Matter and light exhibit both wave and particle properties','Particles are waves','Only light has wave properties',2);
ins($con,'science',7,'Physics','What is the Schrödinger equation?','About classical mechanics','Fundamental equation of quantum mechanics describing wave function','About thermodynamics','About electromagnetism',2);
// Level 8
ins($con,'science',8,'Physics','What is general relativity?','About quantum mechanics','Einstein\'s theory that gravity is curvature of spacetime','About thermodynamics','About electromagnetism',2);
ins($con,'science',8,'Physics','What is a black hole?','A dark star','Region of spacetime where gravity is so strong nothing can escape','A type of galaxy','A type of nebula',2);
ins($con,'science',8,'Physics','What is the Standard Model?','About classical physics','Theory describing fundamental particles and forces (except gravity)','About thermodynamics','About electromagnetism',2);
ins($con,'science',8,'Physics','What is the Higgs boson?','A type of atom','Particle that gives other particles mass','A type of wave','A type of force',2);
ins($con,'science',8,'Physics','What is quantum entanglement?','Classical correlation','Quantum phenomenon where particles are correlated regardless of distance','A type of wave','A type of force',2);
// Level 9
ins($con,'science',9,'Physics','What is string theory?','About classical physics','Theory proposing fundamental particles are one-dimensional strings','About thermodynamics','About electromagnetism',2);
ins($con,'science',9,'Physics','What is dark matter?','Visible matter','Hypothetical matter that does not interact with electromagnetic force','A type of black hole','A type of star',2);
ins($con,'science',9,'Physics','What is dark energy?','Visible energy','Hypothetical energy causing accelerating expansion of the universe','A type of black hole','A type of star',2);
ins($con,'science',9,'Physics','What is the Casimir effect?','About gravity','Attractive force between uncharged conducting plates due to quantum vacuum fluctuations','About thermodynamics','About electromagnetism',2);
ins($con,'science',9,'Physics','What is quantum chromodynamics (QCD)?','About electrons','Theory of strong nuclear force between quarks','About gravity','About electromagnetism',2);
// Level 10
ins($con,'science',10,'Physics','What is the holographic principle?','About optics','Idea that the universe can be described as information on a lower-dimensional boundary','About gravity','About thermodynamics',2);
ins($con,'science',10,'Physics','What is the many-worlds interpretation?','One universe','Interpretation of quantum mechanics where all outcomes occur in parallel universes','About classical physics','About thermodynamics',2);
ins($con,'science',10,'Physics','What is loop quantum gravity?','About classical physics','Theory attempting to quantize gravity using discrete loops of spacetime','About thermodynamics','About electromagnetism',2);
ins($con,'science',10,'Physics','What is the AdS/CFT correspondence?','About classical physics','Duality between string theory in anti-de Sitter space and conformal field theory','About thermodynamics','About electromagnetism',2);
ins($con,'science',10,'Physics','What is the Penrose-Hawking singularity theorem?','About quantum mechanics','Theorem proving existence of singularities in general relativity','About thermodynamics','About electromagnetism',2);

// ============================================================
// SCIENCE — Earth Science
// ============================================================
// Level 1
ins($con,'science',1,'Earth Science','What is the shape of the Earth?','Flat','Spherical (slightly flattened at poles)','Cube','Cylinder',2);
ins($con,'science',1,'Earth Science','What are the layers of the Earth?','Crust, Core, Mantle','Crust, Mantle, Core','Core, Crust, Mantle','Mantle, Core, Crust',2);
ins($con,'science',1,'Earth Science','What causes day and night?','Earth revolving around the Sun','Earth rotating on its axis','Moon revolving around Earth','Sun moving around Earth',2);
ins($con,'science',1,'Earth Science','What causes the seasons?','Earth rotating on its axis','Earth\'s tilted axis as it revolves around the Sun','Moon revolving around Earth','Sun moving around Earth',2);
ins($con,'science',1,'Earth Science','What is a volcano?','A type of mountain with no eruption','An opening in Earth\'s crust through which lava erupts','A type of earthquake','A type of tsunami',2);
// Level 2
ins($con,'science',2,'Earth Science','What is the water cycle?','Cycle of water in the ocean only','Continuous movement of water through evaporation, condensation, and precipitation','Cycle of water in rivers only','Cycle of water in clouds only',2);
ins($con,'science',2,'Earth Science','What is an earthquake?','A volcanic eruption','Shaking of Earth\'s surface caused by movement of tectonic plates','A tsunami','A landslide',2);
ins($con,'science',2,'Earth Science','What is the atmosphere?','The ocean','Layer of gases surrounding Earth','The crust','The mantle',2);
ins($con,'science',2,'Earth Science','What is the ozone layer?','A layer of oxygen','Layer of ozone in the stratosphere that absorbs UV radiation','A layer of carbon dioxide','A layer of nitrogen',2);
ins($con,'science',2,'Earth Science','What is a mineral?','A type of rock','Naturally occurring inorganic solid with a definite chemical composition','A type of fossil','A type of soil',2);
// Level 3
ins($con,'science',3,'Earth Science','What is the rock cycle?','Cycle of rocks in the ocean','Continuous process by which rocks are formed, changed, and reformed','Cycle of rocks in rivers','Cycle of rocks in volcanoes',2);
ins($con,'science',3,'Earth Science','What are the three types of rocks?','Igneous, Sedimentary, Metamorphic','Volcanic, Sedimentary, Crystal','Igneous, Crystal, Metamorphic','Volcanic, Crystal, Sedimentary',1);
ins($con,'science',3,'Earth Science','What is plate tectonics?','Study of rocks','Theory that Earth\'s crust is divided into moving plates','Study of volcanoes','Study of earthquakes',2);
ins($con,'science',3,'Earth Science','What is erosion?','Building up of land','Wearing away of rock and soil by water, wind, or ice','Formation of rocks','Formation of mountains',2);
ins($con,'science',3,'Earth Science','What is the greenhouse effect?','Cooling of Earth','Warming of Earth due to gases trapping heat in the atmosphere','Cooling of the ocean','Warming of the ocean',2);
// Level 4
ins($con,'science',4,'Earth Science','What is the Richter scale?','Measures wind speed','Measures earthquake magnitude','Measures volcanic activity','Measures tsunami height',2);
ins($con,'science',4,'Earth Science','What is a tsunami?','A type of wind','Large ocean wave caused by underwater earthquake or volcanic eruption','A type of storm','A type of flood',2);
ins($con,'science',4,'Earth Science','What is the carbon cycle?','Cycle of carbon in the ocean only','Continuous movement of carbon through atmosphere, biosphere, and geosphere','Cycle of carbon in plants only','Cycle of carbon in animals only',2);
ins($con,'science',4,'Earth Science','What is soil composition?','Only minerals','Minerals, organic matter, water, and air','Only organic matter','Only water',2);
ins($con,'science',4,'Earth Science','What is the hydrosphere?','All land on Earth','All water on Earth','All air on Earth','All living things on Earth',2);
// Level 5
ins($con,'science',5,'Earth Science','What is the Coriolis effect?','Effect of gravity on objects','Deflection of moving objects due to Earth\'s rotation','Effect of the Moon on tides','Effect of the Sun on seasons',2);
ins($con,'science',5,'Earth Science','What is the difference between weather and climate?','No difference','Weather is short-term; climate is long-term patterns','Weather is long-term; climate is short-term','Both are the same',2);
ins($con,'science',5,'Earth Science','What is a biome?','A type of rock','Large ecological area with similar climate and organisms','A type of mineral','A type of soil',2);
ins($con,'science',5,'Earth Science','What is the lithosphere?','The ocean','The rigid outer layer of Earth including crust and upper mantle','The atmosphere','The biosphere',2);
ins($con,'science',5,'Earth Science','What is the asthenosphere?','The rigid outer layer','The semi-fluid layer of the mantle below the lithosphere','The outer core','The inner core',2);
// Level 6
ins($con,'science',6,'Earth Science','What is radiometric dating?','Dating using carbon only','Technique using radioactive decay to determine age of rocks and fossils','Dating using fossils only','Dating using layers only',2);
ins($con,'science',6,'Earth Science','What is the geologic time scale?','A scale for measuring earthquakes','System for dividing Earth\'s history into time intervals','A scale for measuring volcanoes','A scale for measuring erosion',2);
ins($con,'science',6,'Earth Science','What is a fossil?','A type of rock','Preserved remains or traces of ancient organisms','A type of mineral','A type of soil',2);
ins($con,'science',6,'Earth Science','What is the Milankovitch cycle?','About plate tectonics','Cycles of Earth\'s orbital variations affecting climate','About volcanoes','About earthquakes',2);
ins($con,'science',6,'Earth Science','What is ocean acidification?','Increase in ocean pH','Decrease in ocean pH due to absorption of CO₂','Increase in ocean temperature','Decrease in ocean temperature',2);
// Level 7
ins($con,'science',7,'Earth Science','What is the magnetosphere?','The atmosphere','Region around Earth dominated by its magnetic field','The lithosphere','The hydrosphere',2);
ins($con,'science',7,'Earth Science','What is isostasy?','About plate tectonics','Gravitational equilibrium between Earth\'s crust and mantle','About volcanoes','About earthquakes',2);
ins($con,'science',7,'Earth Science','What is the Wilson cycle?','About water','Cycle of opening and closing of ocean basins due to plate tectonics','About volcanoes','About earthquakes',2);
ins($con,'science',7,'Earth Science','What is a subduction zone?','Where plates move apart','Where one tectonic plate moves under another','Where plates slide past each other','Where plates collide and fold',2);
ins($con,'science',7,'Earth Science','What is the ENSO?','El Niño-Southern Oscillation - climate pattern in the Pacific','Earth\'s Natural Seismic Oscillation','Earth\'s Normal Solar Output','El Niño-Solar Oscillation',1);
// Level 8
ins($con,'science',8,'Earth Science','What is the Snowball Earth hypothesis?','Earth was always warm','Hypothesis that Earth was almost entirely frozen multiple times','Earth was always cold','Earth was always the same temperature',2);
ins($con,'science',8,'Earth Science','What is the Great Oxygenation Event?','About volcanoes','Event ~2.4 billion years ago when photosynthesis added oxygen to atmosphere','About earthquakes','About plate tectonics',2);
ins($con,'science',8,'Earth Science','What is the Chicxulub impact?','A volcanic eruption','Asteroid impact ~66 million years ago linked to mass extinction of dinosaurs','An earthquake','A tsunami',2);
ins($con,'science',8,'Earth Science','What is the Younger Dryas?','A warm period','Sudden cold period ~12,900 years ago','A volcanic event','An earthquake event',2);
ins($con,'science',8,'Earth Science','What is the thermohaline circulation?','About wind patterns','Global ocean circulation driven by temperature and salinity differences','About plate tectonics','About volcanic activity',2);
// Level 9
ins($con,'science',9,'Earth Science','What is the Gaia hypothesis?','Earth is a dead planet','Earth is a self-regulating system maintaining conditions for life','Earth is controlled by humans','Earth is controlled by the Sun',2);
ins($con,'science',9,'Earth Science','What is planetary boundary?','Boundary between planets','Limits of Earth system processes that maintain Holocene-like conditions','Boundary between continents','Boundary between oceans',2);
ins($con,'science',9,'Earth Science','What is the deep carbon cycle?','About surface carbon','Movement of carbon through Earth\'s interior over geological timescales','About ocean carbon','About atmospheric carbon',2);
ins($con,'science',9,'Earth Science','What is the concept of the Anthropocene?','A geological era of dinosaurs','Proposed geological epoch defined by human impact on Earth','A geological era of ice ages','A geological era of volcanoes',2);
ins($con,'science',9,'Earth Science','What is geodynamics?','Study of rocks','Study of forces and processes that drive Earth\'s internal dynamics','Study of minerals','Study of fossils',2);
// Level 10
ins($con,'science',10,'Earth Science','What is the concept of deep time?','Short geological time','Vast timescales of Earth\'s history (billions of years)','Human historical time','Recent geological time',2);
ins($con,'science',10,'Earth Science','What is the Earth system science?','Study of one Earth component','Integrated study of all Earth\'s components as a system','Study of rocks only','Study of atmosphere only',2);
ins($con,'science',10,'Earth Science','What is the concept of tipping points in Earth science?','Gradual changes','Thresholds beyond which Earth systems undergo abrupt, irreversible changes','Slow changes','Predictable changes',2);
ins($con,'science',10,'Earth Science','What is the concept of the critical zone?','The inner core','The zone from bedrock to treetops where life interacts with rock, soil, water, and air','The outer core','The mantle',2);
ins($con,'science',10,'Earth Science','What is the concept of biogeochemical cycles?','Cycles of living things only','Cycles of elements through biological, geological, and chemical processes','Cycles of rocks only','Cycles of water only',2);

// ============================================================
// SCIENCE — Scientific Investigation
// ============================================================
// Level 1
ins($con,'science',1,'Scientific Investigation','What is the first step of the scientific method?','Experiment','Observation and asking a question','Conclusion','Hypothesis',2);
ins($con,'science',1,'Scientific Investigation','What is a hypothesis?','A proven fact','An educated guess or prediction','A conclusion','An experiment',2);
ins($con,'science',1,'Scientific Investigation','What is an experiment?','A type of observation','A controlled procedure to test a hypothesis','A type of conclusion','A type of hypothesis',2);
ins($con,'science',1,'Scientific Investigation','What is a conclusion?','A hypothesis','A statement based on the results of an experiment','An observation','An experiment',2);
ins($con,'science',1,'Scientific Investigation','What is data?','A hypothesis','Information collected during an experiment','A conclusion','An observation',2);
// Level 2
ins($con,'science',2,'Scientific Investigation','What is a variable?','A constant value','A factor that can change in an experiment','A type of data','A type of hypothesis',2);
ins($con,'science',2,'Scientific Investigation','What is the independent variable?','The variable that is measured','The variable that is deliberately changed','The variable that stays constant','The variable that is observed',2);
ins($con,'science',2,'Scientific Investigation','What is the dependent variable?','The variable that is changed','The variable that is measured in response to the independent variable','The variable that stays constant','The variable that is controlled',2);
ins($con,'science',2,'Scientific Investigation','What is a control group?','The experimental group','The group that does not receive the experimental treatment','The group that receives the treatment','The group that is observed',2);
ins($con,'science',2,'Scientific Investigation','What is a constant variable?','A variable that changes','A variable that stays the same throughout the experiment','A variable that is measured','A variable that is observed',2);
// Level 3
ins($con,'science',3,'Scientific Investigation','What is qualitative data?','Numerical data','Descriptive data based on observations','Data from experiments','Data from hypotheses',2);
ins($con,'science',3,'Scientific Investigation','What is quantitative data?','Descriptive data','Numerical data that can be measured','Data from observations','Data from conclusions',2);
ins($con,'science',3,'Scientific Investigation','What is accuracy?','How consistent results are','How close a measurement is to the true value','How many trials were done','How many variables were tested',2);
ins($con,'science',3,'Scientific Investigation','What is precision?','How close to the true value','How consistent and repeatable measurements are','How many trials were done','How many variables were tested',2);
ins($con,'science',3,'Scientific Investigation','What is a scientific theory?','A guess','A well-tested explanation supported by extensive evidence','A hypothesis','An observation',2);
// Level 4
ins($con,'science',4,'Scientific Investigation','What is peer review?','Self-review of work','Evaluation of scientific work by other experts in the field','Review by students','Review by teachers',2);
ins($con,'science',4,'Scientific Investigation','What is replication in science?','Doing an experiment once','Repeating an experiment to verify results','Copying someone\'s work','Changing the hypothesis',2);
ins($con,'science',4,'Scientific Investigation','What is a scientific law?','A hypothesis','A description of a natural phenomenon that always occurs under certain conditions','A theory','An observation',2);
ins($con,'science',4,'Scientific Investigation','What is the difference between a theory and a law?','No difference','A theory explains why; a law describes what happens','A law explains why; a theory describes what happens','Both are the same',2);
ins($con,'science',4,'Scientific Investigation','What is bias in scientific research?','Accurate results','Systematic error that skews results in a particular direction','Random error','Precise results',2);
// Level 5
ins($con,'science',5,'Scientific Investigation','What is the null hypothesis?','The hypothesis being tested','The hypothesis that there is no effect or relationship','The alternative hypothesis','The conclusion',2);
ins($con,'science',5,'Scientific Investigation','What is statistical significance?','Any result','Result unlikely to have occurred by chance (usually p < 0.05)','A large sample size','A small sample size',2);
ins($con,'science',5,'Scientific Investigation','What is a sample size?','The size of the laboratory','The number of subjects or observations in a study','The size of the experiment','The size of the data',2);
ins($con,'science',5,'Scientific Investigation','What is random sampling?','Choosing specific subjects','Selecting subjects by chance to reduce bias','Choosing the best subjects','Choosing the easiest subjects',2);
ins($con,'science',5,'Scientific Investigation','What is a double-blind study?','Both groups know the treatment','Neither subjects nor researchers know who receives treatment','Only subjects know the treatment','Only researchers know the treatment',2);
// Level 6
ins($con,'science',6,'Scientific Investigation','What is the placebo effect?','Real treatment effect','Improvement due to belief in treatment rather than actual treatment','No effect','Negative effect',2);
ins($con,'science',6,'Scientific Investigation','What is meta-analysis?','Analysis of one study','Statistical analysis combining results from multiple studies','Analysis of raw data','Analysis of hypotheses',2);
ins($con,'science',6,'Scientific Investigation','What is a systematic review?','Review of one study','Comprehensive review of all available evidence on a topic','Review of raw data','Review of hypotheses',2);
ins($con,'science',6,'Scientific Investigation','What is confounding variable?','The independent variable','A variable that affects the dependent variable and is not controlled','The dependent variable','The control variable',2);
ins($con,'science',6,'Scientific Investigation','What is the Hawthorne effect?','About experiments','Change in behavior due to awareness of being observed','About hypotheses','About conclusions',2);
// Level 7
ins($con,'science',7,'Scientific Investigation','What is Occam\'s razor?','A type of tool','Principle that the simplest explanation is usually correct','A type of experiment','A type of hypothesis',2);
ins($con,'science',7,'Scientific Investigation','What is falsifiability (Popper)?','A theory that is always true','A theory that can be proven false by evidence','A theory that cannot be tested','A theory that is always false',2);
ins($con,'science',7,'Scientific Investigation','What is a paradigm shift (Kuhn)?','Gradual change in science','Revolutionary change in scientific understanding','No change in science','Small change in science',2);
ins($con,'science',7,'Scientific Investigation','What is the demarcation problem?','About experiments','Problem of distinguishing science from non-science','About hypotheses','About conclusions',2);
ins($con,'science',7,'Scientific Investigation','What is abductive reasoning?','Deductive reasoning','Inference to the best explanation','Inductive reasoning','Hypothetical reasoning',2);
// Level 8
ins($con,'science',8,'Scientific Investigation','What is the replication crisis?','About experiments','Widespread failure to reproduce scientific results','About hypotheses','About conclusions',2);
ins($con,'science',8,'Scientific Investigation','What is p-hacking?','A type of experiment','Manipulating data analysis to achieve statistical significance','A type of hypothesis','A type of conclusion',2);
ins($con,'science',8,'Scientific Investigation','What is publication bias?','Publishing all results','Tendency to publish positive results over negative results','Publishing no results','Publishing only negative results',2);
ins($con,'science',8,'Scientific Investigation','What is the file drawer problem?','About filing data','Unpublished studies with negative results that skew the literature','About filing hypotheses','About filing conclusions',2);
ins($con,'science',8,'Scientific Investigation','What is open science?','Closed research','Movement to make scientific research, data, and methods freely accessible','Private research','Classified research',2);
// Level 9
ins($con,'science',9,'Scientific Investigation','What is Bayesian inference?','Frequentist statistics','Updating probability based on prior knowledge and new evidence','Classical statistics','Descriptive statistics',2);
ins($con,'science',9,'Scientific Investigation','What is the philosophy of science?','Study of science only','Study of foundations, methods, and implications of science','Study of experiments','Study of hypotheses',2);
ins($con,'science',9,'Scientific Investigation','What is scientific realism?','Science is not real','View that scientific theories describe reality as it is','Science is only useful','Science is only predictive',2);
ins($con,'science',9,'Scientific Investigation','What is instrumentalism in science?','Science describes reality','View that scientific theories are tools for prediction, not descriptions of reality','Science is always true','Science is always false',2);
ins($con,'science',9,'Scientific Investigation','What is the underdetermination thesis?','One theory fits all data','Multiple theories can be consistent with the same evidence','No theory fits the data','All theories are equal',2);
// Level 10
ins($con,'science',10,'Scientific Investigation','What is the Duhem-Quine thesis?','About single hypotheses','Individual hypotheses cannot be tested in isolation from auxiliary assumptions','About experiments','About conclusions',2);
ins($con,'science',10,'Scientific Investigation','What is the concept of incommensurability (Kuhn)?','Theories can be compared','Theories from different paradigms cannot be directly compared','Theories are always comparable','Theories are always the same',2);
ins($con,'science',10,'Scientific Investigation','What is the strong programme in sociology of science?','Science is objective','Social factors influence the content of scientific knowledge','Science is subjective','Science is neutral',2);
ins($con,'science',10,'Scientific Investigation','What is actor-network theory (Latour)?','About human actors only','Framework treating humans and non-humans as equal actors in networks','About non-human actors only','About social networks only',2);
ins($con,'science',10,'Scientific Investigation','What is the concept of technoscience?','Pure science','Inseparability of science and technology in modern research','Pure technology','Applied science only',2);

// ============================================================
// RESULTS
// ============================================================
echo '<div style="font-family:sans-serif;max-width:600px;margin:40px auto;padding:20px;border:2px solid #4CAF50;border-radius:8px;">';
echo '<h2 style="color:#4CAF50;">✅ Seeding Complete!</h2>';
echo "<p><strong>Inserted:</strong> $inserted questions</p>";
echo "<p><strong>Skipped (already exist):</strong> $skipped questions</p>";
if (!empty($errors)) {
    echo '<p style="color:red;"><strong>Errors:</strong></p><ul>';
    foreach ($errors as $e) echo "<li>$e</li>";
    echo '</ul>';
} else {
    echo '<p style="color:green;">No errors.</p>';
}
echo '<p style="color:#888;font-size:12px;">You can run this again safely — duplicates are skipped.</p>';
echo '</div>';
